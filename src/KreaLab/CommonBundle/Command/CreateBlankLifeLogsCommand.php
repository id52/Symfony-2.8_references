<?php

namespace KreaLab\CommonBundle\Command;

use KreaLab\AdminSkeletonBundle\Entity\Setting;
use KreaLab\CommonBundle\Entity\BlankLifeLog;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateBlankLifeLogsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:create-blanks-logs');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $date \DateTime */
        $date         = new \DateTime();
        $dateInterval = new \DateInterval('PT1S');
        $em           = $this->getContainer()->get('doctrine.orm.entity_manager');
        $cnt          = 0;
        $itemsPerPage = 20;

        $startCntLogs = $em->getRepository('CommonBundle:BlankLifeLog')->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->getQuery()->getSingleScalarResult();

        $lastBlank = $em->getRepository('AdminSkeletonBundle:Setting')->createQueryBuilder('s')
            ->andWhere('s._key = :key')->setParameter('key', 'last_number_for_blanks_migration')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if ($lastBlank) { /** @var  $lastBlank \KreaLab\AdminSkeletonBundle\Entity\Setting */
            $lastId = $lastBlank->getValue();
        } else {
            $minBlankId = $em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->select('MIN(b.id)')
                ->getQuery()->getSingleScalarResult();

            $lastBlank = new Setting();
            $lastBlank->setKey('last_number_for_blanks_migration');
            $lastBlank->setType('integer');
            $lastBlank->setValue($minBlankId);

            $lastId = $minBlankId - 1;
        }

        $output->writeln('Начата обработка бланков с бланка '.$lastId);

        $count = $em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->andWhere('b.id >= :id')->setParameter('id', $lastId)
            ->getQuery()->getSingleScalarResult();
        $pages = ceil($count / $itemsPerPage);


        $arr = [];
        for ($page = 0; $page <= $pages; $page ++) {
            $blanks = $em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->andWhere('b.id > :last_id')->setParameter('last_id', $lastId)
                ->addOrderBy('b.id', 'ASC')
                ->setMaxResults(20)
                ->getQuery()->execute();

            foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));

                $cnt++;
                $status   = $blank->getStatus();
                $stock    = $blank->getStockman();
                $refMan   = $blank->getReferenceman();
                $operator = $blank->getOperator();
                $lastId   = $blank->getId();

                $output->writeln('---- '.$lastId);
                if (in_array($lastId, $arr)) {
                    $output->writeln('повтор ---- '.$lastId);
                }

                $arr[] = $lastId;

                $lastBlank->setValue($lastId);
                $em->persist($lastBlank);
                $em->flush();

                if (!$status) {
                    continue;
                }

                // Создание бланка
                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::S_CREATE_BLANK);
                $lifeLog->setEndStatus('new');
                $lifeLog->setStartUser($stock);
                $lifeLog->setEndUser($stock);
                $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                $lifeLog->setCreatedAt($date);
                $em->persist($lifeLog);

                if ($status == 'new') {
                    continue;
                }

                // назначает конверт справковеду
                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::SR_CREATE_ENVELOP_STOCK_TO_REFERENCE);
                $lifeLog->setEnvelopeType('blank_referenceman_envelope');
                $lifeLog->setEnvelopeId($blank->getReferencemanEnvelope()->getId());

                $lifeLog->setStartStatus('new');
                $lifeLog->setEndStatus('appointedToReferenceman');

                $lifeLog->setStartUser($stock);
                $lifeLog->setEndUser($blank->getReferenceman());
                $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                $lifeLog->setCreatedAt($date);
                $em->persist($lifeLog);

                if ($status == 'appointedToReferenceman') {
                    continue;
                }

                // Кладовщик удаляет не найденные справковедом бланки
                if ($status == 'deletedByStockman') {
                    $lifeLog = new BlankLifeLog();
                    $lifeLog->setBlank($blank);
                    $lifeLog->setOperationStatus($lifeLog::RS_DELETED_BY_STOCKMAN);
                    $lifeLog->setEnvelopeId(null);
                    $lifeLog->setEnvelopeType(null);

                    $lifeLog->setStartStatus('');
                    $lifeLog->setEndStatus($blank->getStatus());

                    $lifeLog->setStartUser($refMan);
                    $lifeLog->setEndUser($stock);
                    $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                    $lifeLog->setCreatedAt($date);
                    $em->persist($lifeLog);

                    continue;
                }

                // Справковед принимает бланки
                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::SR_ACCEPT_BLANK_FROM_STOCK);
                $lifeLog->setEnvelopeId($blank->getReferencemanEnvelope()
                    ->getId());
                $lifeLog->setEnvelopeType('blank_referenceman_envelope');

                $lifeLog->setStartStatus('appointedToReferenceman');
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($blank->getStockman());
                $lifeLog->setEndUser($blank->getReferenceman());
                $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                $lifeLog->setCreatedAt($date);
                $em->persist($lifeLog);

                if ($status == 'acceptedByReferenceman') {
                    continue;
                }

                // Справковед архивирует испорченные бланки
                if ($status == 'archivedByReferenceman') {
                    $lifeLog = new BlankLifeLog();
                    $lifeLog->setBlank($blank);
                    $lifeLog->setOperationStatus($lifeLog::OR_ARHIVED_BY_REFERENCE);

                    $lifeLog->setStartStatus('cancelledByOperator');
                    $lifeLog->setEndStatus($blank->getStatus());

                    $lifeLog->setStartUser($operator);
                    $lifeLog->setEndUser($refMan);
                    $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                    $lifeLog->setCreatedAt($date);
                    $em->persist($lifeLog);

                    continue;
                }

                $env = $blank->getOperatorEnvelope();
                if (!$env) {
                    continue;
                }

                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::RR_CREATE_ENVELOP_REFERENCE_TO_OPERATOR);
                $lifeLog->setEnvelopeId($env->getId());
                $lifeLog->setEnvelopeType('blank_operator_envelope');

                $lifeLog->setStartStatus('acceptedByReferenceman');
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($refMan);
                $lifeLog->setEndUser($refMan);
                $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                $lifeLog->setCreatedAt($date);
                $em->persist($lifeLog);

                // Справковед создает конверт для передачи операторам
                if ($status == 'inEnvelopeForOperator') {
                    continue;
                }

                // Справковед назначает бланк на оператора
                $env = $blank->getOperatorEnvelope();

                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::RO_ASSIGN_ENVELOP_TO_OPERATOR);
                $lifeLog->setEnvelopeId($env->getId());
                $lifeLog->setEnvelopeType('blank_operator_envelope');

                $lifeLog->setStartStatus('acceptedByReferenceman');
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($refMan);
                $lifeLog->setEndUser($operator);
                $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                $lifeLog->setCreatedAt($date);
                $em->persist($lifeLog);

                if ($status == 'appointedToOperator') {
                    continue;
                }

                // Справковед назначает бланк другому справковеду
                if ($status == 'appointedToReferencemanFromReferenceman') {
                    $env      = $blank->getReferencemanReferencemanEnvelope();
                    $oldRefer = $blank->getOldReferenceman();

                    $lifeLog = new BlankLifeLog();
                    $lifeLog->setBlank($blank);
                    $lifeLog->setOperationStatus($lifeLog::RR_REVERT_BLANK_TO_REFERENCE);
                    $lifeLog->setEnvelopeId($env->getId());
                    $lifeLog->setEnvelopeType('blank_referenceman_referenceman_envelope');

                    $lifeLog->setStartStatus('acceptedByReferenceman');
                    $lifeLog->setEndStatus($blank->getStatus());

                    $lifeLog->setStartUser($oldRefer);
                    $lifeLog->setEndUser($refMan);
                    $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                    $lifeLog->setCreatedAt($date);
                    $em->persist($lifeLog);

                    continue;
                }

                // Справковед возвращает бланк кладовщику
                if ($status == 'appointedToStockman') {
                    $env = $blank->getStockmanEnvelope();

                    $lifeLog = new BlankLifeLog();
                    $lifeLog->setBlank($blank);
                    $lifeLog->setOperationStatus($lifeLog::RS_REVERT_BLANK_TO_STOCK);
                    $lifeLog->setEnvelopeId($env->getId());
                    $lifeLog->setEnvelopeType('blank_stockman_envelope');

                    $lifeLog->setStartStatus('acceptedByReferenceman');
                    $lifeLog->setEndStatus($blank->getStatus());

                    $lifeLog->setStartUser($refMan);
                    $lifeLog->setEndUser($stock);
                    $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                    $lifeLog->setCreatedAt($date);
                    $em->persist($lifeLog);

                    continue;
                }

                // Оператор принимае бланки
                $env     = $blank->getOperatorEnvelope();
                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::RO_ACCEPT_BLANK_FROM_REFERENCE);
                $lifeLog->setWorkplace($operator->getWorkplace());
                $lifeLog->setEnvelopeId($env->getId());
                $lifeLog->setEnvelopeType('blank_operator_envelope');

                $lifeLog->setStartStatus('appointedToOperator');
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($blank->getReferenceman());
                $lifeLog->setEndUser($operator);
                $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                $lifeLog->setCreatedAt($date);
                $em->persist($lifeLog);

                if ($status == 'acceptedByOperator') {
                    continue;
                }

                if ($status == 'appointedToReferencemanFromOperator') {
                    $workPlace = $operator->getWorkplace();
                    $env       = $blank->getOperatorReferencemanEnvelope();

                    $lifeLog = new BlankLifeLog();
                    $lifeLog->setBlank($blank);
                    $lifeLog->setOperationStatus($lifeLog::OR_REVERT_BLANK_TO_REFER);
                    $lifeLog->setWorkplace($workPlace);
                    $lifeLog->setEnvelopeId($env->getId());
                    $lifeLog->setEnvelopeType('blank_operator_referenceman_envelope');

                    $lifeLog->setStartStatus('acceptedByOperator');
                    $lifeLog->setEndStatus($blank->getStatus());

                    $lifeLog->setStartUser($operator);
                    $lifeLog->setEndUser($refMan);
                    $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                    $lifeLog->setCreatedAt($date);
                    $em->persist($lifeLog);

                    continue;
                }

                if ($status == 'cancelledByOperator') {
                    $servLog = $blank->getServiceLog();
                    $status  = $lifeLog::OO_CANCELLED_BY_OPERATOR;

                    if ($servLog) {
                        $service  = $servLog->getService();
                        $medError = $servLog->getMedicalCenterError();
                        if ($medError) {
                            if ($service->getIsGnoch()) {
                                $status = $lifeLog::OO_CANCELLED_BY_OPERATOR_MEDICAL_ERROR_GNOCH;
                            } else {
                                $status = $lifeLog::OO_CANCELLED_BY_OPERATOR_MEDICAL_ERROR;
                            }
                        }
                    }

                    $workPlace = $operator->getWorkplace();
                    $env       = $blank->getOperatorEnvelope();

                    $lifeLog = new BlankLifeLog();
                    $lifeLog->setBlank($blank);
                    $lifeLog->setOperationStatus($status);
                    $lifeLog->setWorkplace($workPlace);
                    $lifeLog->setEnvelopeId($env->getId());
                    $lifeLog->setEnvelopeType('blank_operator_envelope');

                    $lifeLog->setStartStatus('acceptedByOperator');
                    $lifeLog->setEndStatus($blank->getStatus());

                    $lifeLog->setStartUser($operator);
                    $lifeLog->setEndUser($operator);

                    $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                    $lifeLog->setCreatedAt($date);
                    $em->persist($lifeLog);

                    continue;
                }

                if ($status == 'usedByOperator') {
                    $service  = $blank->getServiceLog()
                        ->getService();
                    $medError = $blank->getServiceLog()
                        ->getMedicalCenterError();
                    $parent   = $blank->getServiceLog()
                        ->getParent();
                    if ($medError) {
                        $service = $blank->getServiceLog()
                            ->getService();
                        if ($service->getIsGnoch()) {
                            $status = $lifeLog::OO_USED_BY_OPERATOR_BY_MEDICAL_ERROR_GNOCH;
                        } else {
                            $status = $lifeLog::OO_USED_BY_OPERATOR_BY_MEDICAL_ERROR;
                        }
                    } else {
                        $status = $lifeLog::OO_USED_BY_OPERATOR;
                    }

                    $lifeLog->setBlank($blank);
                    $lifeLog->setOperationStatus($status);
                    $lifeLog->setWorkplace($operator->getWorkplace());
                    $lifeLog->setEnvelopeId($env->getId());
                    $lifeLog->setEnvelopeType('blank_operator_envelope');

                    $lifeLog->setStartStatus('acceptedByOperator');
                    $lifeLog->setEndStatus($blank->getStatus());
                    $lifeLog->setServiceName($service->getName());

                    if ($medError) {
                        $coorectBlanks = $blank->getServiceLog()
                            ->getMedicalCenterCorrects();
                        if (count($coorectBlanks)) {
                            $sombadyBlank = $coorectBlanks[0];
                            $lifeLog->setCorrectBlankNumber($sombadyBlank->getId());
                        }
                    } elseif ($parent) {
                        $lifeLog->setCorrectBlankNumber($parent->getBlank()
                            ->getNumber());
                    }

                    $lifeLog->setStartUser($operator);
                    $lifeLog->setEndUser($operator);

                    $date = new \DateTime($date->add($dateInterval)->format('Y-m-d H:i:s'));
                    $lifeLog->setCreatedAt($date);
                    $em->persist($lifeLog);
                }
            }

            $em->flush();
            $em->clear();

            $lastBlank = $em->getRepository('AdminSkeletonBundle:Setting')->createQueryBuilder('s')
                ->andWhere('s._key = :key')->setParameter('key', 'last_number_for_blanks_migration')
                ->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
        }

        $maxBlankId = $em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('MAX(b.id)')
            ->getQuery()->getSingleScalarResult();

        if ($maxBlankId == $lastId) {
            $em->remove($lastBlank);
            $em->flush();
        }

        $cntLogs = $em->getRepository('CommonBundle:BlankLifeLog')->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->getQuery()->getSingleScalarResult();

        $output->writeln('Обработанно '.$cnt.' бланков. Выполнено '.$page.' страницы из '.$pages.' страниц');
        $output->writeln('Последний обработанный бланк с номером '.$lastId.' максимальный id '.$maxBlankId);
        $output->writeln('Создано '.($cntLogs - $startCntLogs).' логов ----- '.count($arr));
    }
}
