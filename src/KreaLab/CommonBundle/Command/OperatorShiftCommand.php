<?php

/**
 * Added in crontab
 * 5 0 * * *
 */

namespace KreaLab\CommonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class OperatorShiftCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:operator-shift')
            ->addOption('cron', 'c', InputOption::VALUE_NONE)
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $yesterday = new \DateTime('yesterday');

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $shiftLogs = $em->getRepository('CommonBundle:ShiftLog')->createQueryBuilder('s')
            ->andWhere('s.date = :date')->setParameter('date', $yesterday)
            ->getQuery()->execute();

        foreach ($shiftLogs as $log) { /** @var $log \KreaLab\CommonBundle\Entity\ShiftLog */
            $output->write($log->getDate()->format('Y-m-d'));
            $output->write(',');
            $output->write($log->getUser()->getFullName());
            $output->write(',');
            $output->write($log->getFilial()->getName());
            $output->write(',');
            $output->write($log->getStartTime()->format('Y-m-d H:i:s'));
            $output->write(',');
            if (!$log->getEndTime()) { /** @var $filialSchedule \KreaLab\CommonBundle\Entity\Schedule */
                $filials = $log->getUser()->getFilials();
                $filial  = $filials[0];

                $filialSchedule = $em->getRepository('CommonBundle:Schedule')
                    ->createQueryBuilder('s')
                    ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
                    ->andWhere('s.date = :date')->setParameter('date', $yesterday)
                    ->getQuery()->getOneOrNullResult();

                if ($filialSchedule) {
                    $log->setEndTime($filialSchedule->getEndTime());
                    $em->persist($log);
                    $em->flush();
                }
            } else {
                $output->write($log->getEndTime()->format('Y-m-d H:i:s'));
            }

            $output->write(',');
            $output->write((int)$log->getClosed());

            $actionLog = $em->getRepository('CommonBundle:ActionLog')->createQueryBuilder('a')
                ->andWhere('a.user = :user')->setParameter('user', $log->getUser())
                ->setMaxResults(1)
                ->orderBy('a.id', 'desc')
                ->getQuery()->getOneOrNullResult(); /** @var $actionLog \KreaLab\CommonBundle\Entity\ActionLog */

            $output->write(',');
            $output->write($actionLog->getCreatedAt()->format('Y-m-d H:i:s'));

            $output->writeln('');
        }
    }
}
