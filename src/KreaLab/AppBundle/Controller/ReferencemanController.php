<?php

namespace KreaLab\AppBundle\Controller;

use KreaLab\CommonBundle\Entity\BlankLifeLog;
use KreaLab\CommonBundle\Entity\BlankOperatorEnvelope;
use KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope;
use KreaLab\CommonBundle\Entity\BlankStockmanEnvelope;
use KreaLab\CommonBundle\Entity\ReferencemanArchiveBox;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Doctrine\ORM\EntityRepository;

/** @Route("/referenceman-blanks") */
class ReferencemanController extends Controller
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
        $this->denyAccessUnlessGranted('ROLE_REFERENCEMAN');
    }

    /** @Route("/referenceman-operator-envelopes/view-after-creating-{id}/",
     *      name="referenceman_blanks__referenceman_operator_envelope__view_after_creating") */
    public function stockmanBlanksViewStockmanReferencemanEnvelopeAfterCreating($id)
    {
        $envelope = $this->em->getRepository('CommonBundle:BlankOperatorEnvelope')->createQueryBuilder('boe')
            ->leftJoin('boe.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('boe.blanks', 'b')->addSelect('b')
            ->andWhere('boe.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('boe.operator_applied is null')
            ->andWhere('boe.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        if (!$envelope) {
            throw $this->createNotFoundException();
        }

        $referenceTypes = [];
        foreach ($this->em->getRepository('CommonBundle:ReferenceType')->findAll() as $referenceType) {
            $referenceTypes[$referenceType->getId()] = $referenceType;
        }

        $legalEntities = [];
        foreach ($this->em->getRepository('CommonBundle:LegalEntity')->findAll() as $legalEntity) {
            $legalEntities[$legalEntity->getId()] = $legalEntity;
        }

        return $this->render('AppBundle:Referenceman:view_envelope_for_operator_after_creating.html.twig', [
            'envelope'        => $envelope,
            'filter_form'     => null,
            'reference_types' => $referenceTypes,
            'legal_entities'  => $legalEntities,
        ]);
    }

    /** @Route("/stockman-envelopes/view-intervals-{id}/",
     *     name="referenceman_blanks__stockman_envelope__view_intervals") */
    public function referencemanBlanksViewIntervalsStockmanReferencemanEnvelopeAction($id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope */
        $envelope = $this->em->getRepository('CommonBundle:BlankReferencemanEnvelope')->findOneBy([
            'id'                   => $id,
            'referenceman'         => $this->getUser(),
            'referenceman_applied' => null,
        ]);

        if (!$envelope) {
            throw $this->createNotFoundException('Envelope is not found');
        }

        $intervals = $envelope->getIntervals();
        $intervals = $intervals[$envelope->getLegalEntity()->getId()][$envelope->getReferenceType()->getId()];
        $intervals = array_pop($intervals);

        return $this->render('AppBundle:Referenceman:envelope_from_stockman__view_intervals.html.twig', [
            'intervals' => $intervals,
            'envelope'  => $envelope,
        ]);
    }

    /** @Route("/stockman-envelopes/view-{id}/",
     *     name="referenceman_blanks__stockman_envelope__view") */
    public function referencemanBlanksViewStockmanReferencemanEnvelopeAction(Request $request, $id)
    {
        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->leftJoin('b.referenceman_envelope', 're')->addSelect('re')
            ->leftJoin('b.reference_type', 'rt')->addSelect('rt')
            ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('b.status = :status')->setParameter('status', 'appointedToReferenceman')
            ->andWhere('re.id = :id')->setParameter('id', $id)
            ->andWhere('re.referenceman_applied IS NULL');

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(100);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Referenceman:view_envelope_from_stockman.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /** @Route("/stockman-envelopes/get-{id}/", name="referenceman_blanks_get_stockman_envelope") */
    public function referencemanBlanksGetStockmanEnvelopeAction($id)
    {
        $envelope = $this->em->find('CommonBundle:BlankReferencemanEnvelope', $id);
        if (!$envelope) {
            throw $this->createNotFoundException();
        }

        $envelope->setReferencemanApplied(new \DateTime());
        $this->em->persist($envelope);
        $this->em->flush();

        $blanks = $this->em->getRepository('CommonBundle:Blank')->findBy([
            'referenceman_envelope' => $envelope,
        ]);

        /** @var $user \KreaLab\CommonBundle\Entity\User */
        $user = $this->getUser();

        $cnt = 0;
        foreach ($blanks as $blank) { /** @var  $blank \KreaLab\CommonBundle\Entity\Blank */
            $oldStatus = $blank->getStatus();
            $blank->setStatus('acceptedByReferenceman');
            $blank->setReferencemanApplied(new \DateTime());
            $this->em->persist($blank);

            $lifeLog = new BlankLifeLog();
            $lifeLog->setBlank($blank);
            $lifeLog->setOperationStatus($lifeLog::SR_ACCEPT_BLANK_FROM_STOCK);
            $lifeLog->setEnvelopeId($blank->getReferencemanEnvelope()->getId());
            $lifeLog->setEnvelopeType('blank_referenceman_envelope');

            $lifeLog->setStartStatus($oldStatus);
            $lifeLog->setEndStatus($blank->getStatus());

            $lifeLog->setStartUser($blank->getStockman());
            $lifeLog->setEndUser($user);
            $this->em->persist($lifeLog);

            $user->addReferencemanInterval(
                $blank->getLegalEntity(),
                $blank->getReferenceType(),
                $blank->getSerie(),
                $blank->getNumber(),
                1,
                $blank->getLeadingZeros()
            );

            if ($cnt % 100 == 0) {
                $this->em->flush();

                $this->em->detach($blank);
                $this->em->detach($lifeLog);
            }

            $cnt ++;
        }

        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash('success', 'Приняли.');
        return $this->redirectToRoute('referenceman_blanks_stockman_envelopes');
    }

    /** @Route("/stockman-envelopes/", name="referenceman_blanks_stockman_envelopes") */
    public function referencemanBlanksStockmanEnvelopesAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:BlankReferencemanEnvelope')->createQueryBuilder('bre')
            ->leftJoin('bre.reference_type', 'rt')->addSelect('rt')
            ->andWhere('bre.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('bre.referenceman_applied IS NULL')
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Referenceman:envelopes_from_stockman.html.twig', [
            'pagerfanta'  => $pagerfanta,
            'filter_form' => null,
            'list_fields' => [
                'id',
                'stockman',
                ['legalEntityShortName', 'min_col text-left'],
                ['reference_type', 'min_col text-left'],
                ['serie', 'min_col text-left'],
                ['first_num', 'min_col text-right'],
                ['amount', 'min_col text-right'],
            ],
        ]);
    }

    /** @Route("/operator-envelopes/", name="referenceman_blanks_operator_envelopes") */
    public function referencemanBlanksOperatorEnvelopes(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('envelope_id', IntegerType::class, [
            'label'       => 'Номер конверта',
            'attr'        => ['min' => 1],
            'required'    => false,
            'constraints' => [
                new Assert\GreaterThan(0),
            ],
        ]);
        $fb->add('last_name', TextType::class, [
            'label'    => 'Фамилия',
            'required' => false,
        ]);
        $fb->add('first_name', TextType::class, [
            'label'    => 'Имя',
            'required' => false,
        ]);
        $fb->add('patronymic', TextType::class, [
            'label'    => 'Отчество',
            'required' => false,
        ]);

        $fb->add('legal_entity', EntityType::class, [
            'label'        => 'Юридическое лицо',
            'class'        => 'CommonBundle:LegalEntity',
            'placeholder'  => ' - Выберите юридическое лицо - ',
            'required'     => false,
            'choice_label' => 'nameAndShortName',
        ]);

        $fb->add('reference_type', EntityType::class, [
            'label'       => 'Тип бланка',
            'class'       => 'CommonBundle:ReferenceType',
            'placeholder' => ' - Выберите тип бланка - ',
            'required'    => false,
        ]);

        $series = $this->em->getRepository('CommonBundle:BlankOperatorEnvelope')->createQueryBuilder('boe')
            ->select('DISTINCT(boe.serie) AS serie')
            ->andWhere('boe.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('boe.operator_applied IS NULL')
            ->orderBy('serie')
            ->getQuery()->execute();

        $seriesChoices['- Выберите серию -'] = '';
        foreach ($series as $serie) {
            $seriesChoices[$serie['serie']] = $serie['serie'];
        }

        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'choices'           => $seriesChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);

        $statusChoices['- Выберите статус -'] = '';
        $statusChoices['Назначены']           = 'appointedToOperator';
        $statusChoices['Не назначены']        = 'inEnvelopeForOperator';

        $fb->add('status', ChoiceType::class, [
            'label'             => 'Статус',
            'choices'           => $statusChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);


        $stampChoices['- Выберите статус печати -'] = '';
        $stampChoices['С печатью']                  = 'with_stamp';
        $stampChoices['Без печати']                 = 'with_no_stamp';

        $fb->add('stamp', ChoiceType::class, [
            'label'             => 'Печать',
            'choices'           => $stampChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);

        $form = $fb->getForm();

        $qb = $this->em->getRepository('CommonBundle:BlankOperatorEnvelope')->createQueryBuilder('boe')
            ->leftJoin('boe.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('boe.operator', 'o')->addSelect('o')
            ->andWhere('boe.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('boe.operator_applied IS NULL')
        ;

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->get('envelope_id')->getData();
            if ($data) {
                $qb->andWhere('boe.id = :envelope_id')->setParameter('envelope_id', $data);
            }

            $data = $form->get('last_name')->getData();
            if ($data) {
                $qb->andWhere('o.last_name LIKE :last_name')->setParameter('last_name', '%'.$data.'%');
            }

            $data = $form->get('first_name')->getData();
            if ($data) {
                $qb->andWhere('o.first_name LIKE :first_name')->setParameter('first_name', '%'.$data.'%');
            }

            $data = $form->get('patronymic')->getData();
            if ($data) {
                $qb->andWhere('o.patronymic LIKE :patronymic')->setParameter('patronymic', '%'.$data.'%');
            }

            $data = $form->get('legal_entity')->getData();
            if ($data) {
                $qb->andWhere('boe.legal_entity = :legal_entity')->setParameter('legal_entity', $data);
            }

            $data = $form->get('reference_type')->getData();
            if ($data) {
                $qb->andWhere('boe.reference_type = :reference_type')->setParameter('reference_type', $data);
            }

            $data = $form->get('serie')->getData();
            if ($data) {
                $qb->andWhere('boe.serie = :serie')->setParameter('serie', $data);
            }

            $data = $form->get('status')->getData();
            if ($data == 'appointedToOperator') {
                $qb->andWhere('boe.operator is NOT NULL');
            }

            if ($data == 'inEnvelopeForOperator') {
                $qb->andWhere('boe.operator is NULL');
            }

            $data = $form->get('stamp')->getData();
            if ($data == 'with_stamp') {
                $qb->andWhere('boe.stamp = :stamp')->setParameter('stamp', true);
            }

            if ($data == 'with_no_stamp') {
                $qb->andWhere('boe.stamp = :stamp')->setParameter('stamp', false);
            }
        }


        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        $referenceTypes = [];
        foreach ($this->em->getRepository('CommonBundle:ReferenceType')->findAll() as $referenceType) {
            $referenceTypes[$referenceType->getId()] = $referenceType;
        }

        $legalEntities = [];
        foreach ($this->em->getRepository('CommonBundle:LegalEntity')->findAll() as $legalEntity) {
            $legalEntities[$legalEntity->getId()] = $legalEntity->getShortName();
        }

        return $this->render('AppBundle:Referenceman:envelopes_for_operator.html.twig', [
            'pagerfanta'      => $pagerfanta,
            'reference_types' => $referenceTypes,
            'legal_entities'  => $legalEntities,
            'filter_form'     => $form->createView(),
            'list_fields'     => [
                ['id', 'min_col text-right'],
                ['legalEntityShortName', 'min_col text-right'],
                ['referenceType', 'text-right'],
                ['stamp', 'min_col text-right'],
                ['serie', 'min_col text-right'],
                ['countIntervals', 'min_col text-right'],
                ['operator', 'text-left'],
            ],
        ]);
    }

    /** @Route("/operator-envelopes/view-intervals-{id}/",
     *      name="referenceman_blanks_view_intervals_operator_envelope") */
    public function referencemanBlanksViewIntervalsOperatorEnvelope($id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
        $envelope = $this->em->getRepository('CommonBundle:BlankOperatorEnvelope')->findOneBy([
            'id'           => $id,
            'referenceman' => $this->getUser(),
        ]);

        if (!$envelope) {
            throw $this->createNotFoundException('Envelope is not found');
        }

        $intervals = $envelope->getIntervals();
        $intervals = $intervals[$envelope->getLegalEntity()->getId()][$envelope->getReferenceType()->getId()];
        $intervals = array_pop($intervals);

        return $this->render('AppBundle:Referenceman:view_intervals_envelope_for_operator.html.twig', [
            'intervals' => $intervals,
            'envelope'  => $envelope,
        ]);
    }

    /** @Route("/operator-envelopes/view-{id}/",
     *      name="referenceman_blanks_view_operator_envelope") */
    public function referencemanBlanksViewOperatorEnvelope($id)
    {
        $envelope = $this->em->getRepository('CommonBundle:BlankOperatorEnvelope')->createQueryBuilder('boe')
            ->leftJoin('boe.reference_type', 'rt')->addSelect('rt')
//            ->leftJoin('boe.blanks', 'b')->addSelect('b')
            ->andWhere('boe.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('boe.operator_applied is null')
            ->andWhere('boe.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
        if (!$envelope) {
            throw $this->createNotFoundException();
        }

        return $this->render('AppBundle:Referenceman:view_envelope_for_operator.html.twig', [
            'envelope' => $envelope,
        ]);
    }

    /** @Route("/operator-envelopes/delete-blank-from-operator-envelope-{id}/",
     *      name="referenceman_blanks_delete_blank_from_operator_envelope") */
    public function referencemanBlanksDeleteBlankFromOperatorEnvelopeEnvelope($id)
    {
        /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
        $blank = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.id = :id')->setParameter('id', $id)
            ->andWhere('b.referenceman =:referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('b.status =:status')->setParameter('status', 'inEnvelopeForOperator')
            ->andWhere('b.operator IS NULL')
            ->getQuery()->getOneOrNullResult();
        if (!$blank) {
            throw $this->createNotFoundException();
        }

        $fName = $this->get('kernel')->getCacheDir().'/referenceman_envelope_'.$blank->getOperatorEnvelope()->getId();
        if (!file_exists($fName)) {
            file_put_contents($fName, microtime(true) - 1);
        }

        $sTime = (float)file_get_contents($fName);
        if (microtime(true) < $sTime) {
            file_put_contents($fName, $sTime + 1);
            sleep(ceil($sTime - microtime(true)));
        } else {
            $sTime = microtime(true) + 1;
            file_put_contents($fName, $sTime);
        }

        $oldStatus = $blank->getStatus();
        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
        $envelope = $blank->getOperatorEnvelope();

        $operator = $envelope->getOperator();
        $envelope->removeInterval(
            $blank->getLegalEntity(),
            $blank->getReferenceType(),
            $blank->getSerie(),
            $blank->getNumber(),
            1,
            $blank->getLeadingZeros()
        );

        $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->update()
            ->set('b.operator_envelope', 'NULL')
            ->andWhere('b = :blank')->setParameter('blank', $blank)
            ->getQuery()->execute();

        $referenceman = $this->em->find('CommonBundle:User', $this->getUser()->getId());
        $this->em->refresh($referenceman);
        $referenceman->addReferencemanInterval(
            $blank->getLegalEntity(),
            $blank->getReferenceType(),
            $blank->getSerie(),
            $blank->getNumber(),
            1,
            $blank->getLeadingZeros()
        );
        $this->em->persist($referenceman);
        $this->em->flush();

        $blank->setOperatorEnvelope(null);
        $blank->setStatus('acceptedByReferenceman');

        $this->em->persist($blank);
        $this->em->flush();

        $lifeLog = new BlankLifeLog();
        $lifeLog->setBlank($blank);
        $lifeLog->setOperationStatus($lifeLog::RR_REMOVE_BLANK_IN_ENVELOP_FOR_OPERATOR);
        $lifeLog->setEnvelopeId(null);
        $lifeLog->setEnvelopeType(null);

        $lifeLog->setStartStatus($oldStatus);
        $lifeLog->setEndStatus($blank->getStatus());

        $lifeLog->setStartUser($operator ? $operator : $this->getUser());
        $lifeLog->setEndUser($this->getUser());

        $this->em->persist($lifeLog);
        $this->em->flush();

        return $this->redirectToRoute('referenceman_blanks_view_operator_envelope', ['id' => $envelope->getId()]);
    }

    /** @Route("/operator-envelopes/remove-operator-{id}/",
     *      name="referenceman_blanks_operator_envelope_remove_operator") */
    public function referencemanBlanksOperatorEnvelopeRemoveOperator($id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
        $envelope = $this->em->getRepository('CommonBundle:BlankOperatorEnvelope')->createQueryBuilder('boe')
            ->leftJoin('boe.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('boe.blanks', 'b')->addSelect('b')
            ->andWhere('boe.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('boe.operator_applied is null')
            ->andWhere('boe.operator is not null')
            ->andWhere('boe.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        if ($envelope) {
            $oldOperator = $envelope->getOperator();
            $envelope->setOperator(null);
            $blanks = $envelope->getBlanks();
            foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                $blank->setStatus('inEnvelopeForOperator');
                $blank->setOperator(null);
                $this->em->persist($blank);

                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::OR_REMOVE_ENVELOP_FROM_OPERATOR);
                $lifeLog->setEnvelopeId($envelope->getId());
                $lifeLog->setEnvelopeType('blank_operator_envelope');

                $lifeLog->setStartStatus($blank->getStatus());
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($oldOperator);
                $lifeLog->setEndUser($this->getUser());

                $this->em->persist($lifeLog);
            };

            $this->em->persist($envelope);
            $this->em->flush();
        }

        return $this->redirectToRoute('referenceman_blanks_operator_envelopes');
    }

    /** @Route("/operator-envelopes/remove-envelope-{id}/",
     *      name="referenceman_blanks_operator_envelope_remove_envelope") */
    public function referencemanBlanksOperatorEnvelopeRemoveEnvelope($id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
        $envelope = $this->em->getRepository('CommonBundle:BlankOperatorEnvelope')->createQueryBuilder('boe')
            ->leftJoin('boe.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('boe.blanks', 'b')->addSelect('b')
            ->andWhere('boe.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('boe.operator_applied is null')
            ->andWhere('boe.operator is null')
            ->andWhere('boe.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        if ($envelope) {
            $this->em->beginTransaction();
            $blanks = $envelope->getBlanks();

            foreach ($blanks as &$blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                $oldStatus = $blank->getStatus();
                $blank->setOperatorEnvelope(null);
                $blank->setStatus('acceptedByReferenceman');
                $this->em->persist($blank);

                $referenceman = $this->getUser(); /** @var $referenceman \KreaLab\CommonBundle\Entity\User */
                $referenceman->addReferencemanInterval(
                    $blank->getLegalEntity(),
                    $blank->getReferenceType(),
                    $blank->getSerie(),
                    $blank->getNumber(),
                    1,
                    $blank->getLeadingZeros()
                );

                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::RR_DELETE_ENVELOP_BY_REFERENCE);
                $lifeLog->setEnvelopeId($envelope->getId());
                $lifeLog->setEnvelopeType('blank_operator_envelope');

                $lifeLog->setStartStatus($oldStatus);
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($this->getUser());
                $lifeLog->setEndUser($this->getUser());

                $this->em->persist($lifeLog);
            };

            $this->em->remove($envelope);
            $this->em->flush();
            $this->em->clear();
            $this->em->commit();
        }

        return $this->redirectToRoute('referenceman_blanks_operator_envelopes');
    }

    /** @Route("/referenceman-cancelled/add/",
     *     name="referenceman_blanks_add_referenceman_cancelled") */
    public function referencemanBlanksAddReferencemanCancelledAction(Request $request)
    {
        $lEntityId = $request->get('lEntityId');
        $refTypeId = $request->get('refTypeId');
        $amount    = $request->get('amount', 1);
        $serieIn   = $request->get('serieIn');
        $number    = $request->get('number');

        $data = [];

        if (!empty($number)) {
            $data['number'] = $number;
        }

        if ($lEntityId) {
            $legalEntity = $this->em->getRepository('CommonBundle:LegalEntity')->find($lEntityId);
            if (!$legalEntity) {
                throw $this->createNotFoundException('Legal entity not found');
            }

            $data['legal_entity'] = $legalEntity;
        }

        if ($refTypeId) {
            $referenceType = $this->em->getRepository('CommonBundle:ReferenceType')->find($refTypeId);
            if (!$referenceType) {
                throw $this->createNotFoundException('Reference type not found');
            }

            $data['reference_type'] = $referenceType;
        }

        $serie         = null;
        $seriesChoices = [];
        if (!empty($serieIn) and $serieIn != '-') {
            $serie = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->select('DISTINCT(b.serie) AS serie')
                ->andWhere('b.serie = :serie')->setParameter('serie', $serieIn)
                ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
                ->getQuery()->getSingleScalarResult();
            if (!$serie) {
                throw $this->createNotFoundException('Serie not found');
            }

            $data['serie']         = $serie;
            $seriesChoices[$serie] = $serie;
        } else {
            $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->select('DISTINCT(b.serie) AS serie')
                ->andWhere('b.serie IS NOT NULL')
                ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
                ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                ->orderBy('serie')
                ->getQuery()->execute();
            foreach ($series as $serie) {
                $seriesChoices[$serie['serie']] = $serie['serie'];
            }
        }

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('legal_entity', EntityType::class, [
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'choice_label'  => 'nameAndShortName',
            'query_builder' => function (EntityRepository $er) use ($lEntityId) {
                if ($lEntityId) {
                    return $er->createQueryBuilder('le')
                        ->andWhere('le.id = :id')->setParameter('id', $lEntityId)
                        ->innerJoin('le.blanks', 'b', 'WITH', 'b.status = :status')
                        ->setParameter('status', 'acceptedByReferenceman')
                        ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                        ;
                } else {
                    return $er->createQueryBuilder('le')
                        ->andWhere('le.active = :active')->setParameter('active', true)
                        ->innerJoin('le.blanks', 'b', 'WITH', 'b.status = :status')
                        ->setParameter('status', 'acceptedByReferenceman')
                        ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                        ;
                }
            },
        ]);
        $fb->add('reference_type', EntityType::class, [
            'label'         => 'Тип справки',
            'class'         => 'CommonBundle:ReferenceType',
            'placeholder'   => ' - Выберите тип справки - ',
            'constraints'   => new Assert\NotBlank(),
            'query_builder' => function (EntityRepository $er) use ($refTypeId) {
                if ($refTypeId) {
                    return $er->createQueryBuilder('rt')
                        ->andWhere('rt.id = :id')->setParameter('id', $refTypeId)
                        ->innerJoin('rt.blanks', 'b', 'WITH', 'b.status = :status')
                        ->setParameter('status', 'acceptedByReferenceman')
                        ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                    ;
                } else {
                    return $er->createQueryBuilder('rt')
                        ->innerJoin('rt.blanks', 'b', 'WITH', 'b.status = :status')
                        ->setParameter('status', 'acceptedByReferenceman')
                        ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                        ;
                }
            },
        ]);
        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'placeholder'       => ' - Выберите серию - ',
            'choices'           => $seriesChoices,
            'choices_as_values' => true,
            'required'          => false,
        ]);
        $fb->add('number', TextType::class, [
            'label'       => 'Номер бланка',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
        $fb->add('save', SubmitType::class, [
            'label' => 'Подтвердить',
            'attr'  => ['class' => 'btn-success'],
        ]);
        $fb->setData($data);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy([
                'serie'          => $form->get('serie')->getData(),
                'number'         => (int)$form->get('number')->getData(),
                'reference_type' => $form->get('reference_type')->getData(),
                'legal_entity'   => $form->get('legal_entity')->getData(),
                'status'         => 'acceptedByReferenceman',
                'referenceman'   => $this->getUser(),
                'leading_zeros'  => strlen($form->get('number')->getData()),
            ]);

            if ($blank) {
                $oldStatus = $blank->getStatus();
                $blank->setStatus('cancelledByReferenceman');
                $this->em->persist($blank);
                $user = $this->getUser(); /** @var $user \KreaLab\CommonBundle\Entity\User */
                if (!empty($user->getReferencemanIntervals())) {
                    $user->removeReferencemanInterval(
                        $blank->getLegalEntity(),
                        $blank->getReferenceType(),
                        $blank->getSerie(),
                        $blank->getNumber(),
                        1,
                        $blank->getLeadingZeros()
                    );
                }

                $this->em->persist($user);

                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::RR_ADDED_NOT_FOUND_BLANK);
                $lifeLog->setEnvelopeId(null);
                $lifeLog->setEnvelopeType(null);

                $lifeLog->setStartStatus($oldStatus);
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($user);
                $lifeLog->setEndUser($user);

                $this->em->persist($lifeLog);

                $this->em->flush();

                if ($number) {
                    if ($blank->getSerie()) {
                        return $this->redirectToRoute('referenceman_blanks_not_in_operator_envelopes__view_list', [
                            'lEntityId' => $blank->getLegalEntity()->getId(),
                            'refTypeId' => $blank->getReferenceType()->getId(),
                            'serie'     => $blank->getSerie(),
                        ]);
                    } else {
                        return $this->redirectToRoute('referenceman_blanks_not_in_operator_envelopes__view_list', [
                            'lEntityId' => $blank->getLegalEntity()->getId(),
                            'refTypeId' => $blank->getReferenceType()->getId(),
                            'serie'     => '-',
                        ]);
                    }
                }

                if ($lEntityId) {
                    $this->addFlash('success', 'Передали');
                    return $this->redirectToRoute('referenceman_blanks_not_in_operator_envelopes');
                } else {
                    $this->addFlash('success', 'Ненайденный бланк добавлен');
                    return $this->redirectToRoute('referenceman_blanks_referenceman_cancelled');
                }
            } else {
                $this->addFlash('danger', 'Не найден');
            }
        }

        $referenceTypes = $this->em->getRepository('CommonBundle:ReferenceType')->createQueryBuilder('rt')
            ->addSelect('rt')->getQuery()->getArrayResult();

        $referenceTypesIdKeys = [];
        foreach ($referenceTypes as $referenceType) {
            $referenceTypesIdKeys[$referenceType['id']] = $referenceType;
        }

        $info = [];

        if ($lEntityId and $refTypeId and empty($number)) {
            $legalEntity   = $this->em->getRepository('CommonBundle:LegalEntity')->find($lEntityId);
            $referenceType = $this->em->getRepository('CommonBundle:ReferenceType')->find($refTypeId);

            $info['legalEntity']   = $legalEntity->getName();
            $info['referenceType'] = $referenceType->getName();
            $info['serie']         = $serieIn;
            $info['amount']        = $amount;
            $info['interval']      = '';
            $intervalsArr          = [];
            $referenceman          = $this->getUser(); /** @var $referenceman \KreaLab\CommonBundle\Entity\User */
            $referencemanIntervals = $referenceman->getReferencemanIntervals();
            $intervals             = $referencemanIntervals[$legalEntity->getId()][$referenceType->getId()][$serieIn];

            foreach ($intervals as $leadingZero => $curIntervals) {
                foreach ($curIntervals as $int) {
                    $int[0]         = str_pad($int[0], $leadingZero, '0', STR_PAD_LEFT);
                    $int[1]         = str_pad($int[1], $leadingZero, '0', STR_PAD_LEFT);
                    $intervalsArr[] = ($int[0] == $int[1]) ? '['.$int[0].']' : '['.$int[0].', '.$int[1].']';

                    $intervalsArr['interval'] = ($int[0] == $int[1]) ? '['.$int[0].']' : '['.$int[0].', '.$int[1].']';
                    $intervalsArr['amount']   = $int[1] - $int[0] + 1;
                    $info['intervals'][]      = $intervalsArr;
                }
            }
        }

        return $this->render('AppBundle:Referenceman:cancel_blank.html.twig', [
            'reference_types' => $referenceTypesIdKeys,
            'form'            => $form->createView(),
            'info'            => $info,
        ]);
    }

    /**
     * @Route("/", name="referenceman_blanks__referenceman_cancelled")
     * @Route("/referenceman-cancelled/", name="referenceman_blanks_referenceman_cancelled")
     * */
    public function referencemanBlanksReferencemanCancelledAction(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);

        $fb->add('legal_entity', EntityType::class, [
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'required'      => false,
            'choice_label'  => 'nameAndShortName',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->innerJoin('le.blanks', 'b', 'WITH', 'b.status = :status')
                    ->setParameter('status', 'cancelledByReferenceman')
                    ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                    ;
            },
        ]);

        $fb->add('reference_type', EntityType::class, [
            'label'         => 'Тип бланка',
            'class'         => 'CommonBundle:ReferenceType',
            'placeholder'   => ' - Выберите тип бланка - ',
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('rt')
                    ->innerJoin('rt.blanks', 'b', 'WITH', 'b.status = :status')
                    ->setParameter('status', 'cancelledByReferenceman')
                    ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                    ;
            },
        ]);

        $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT(b.serie) AS serie')
            ->andWhere('b.serie != :serie')->setParameter('serie', '')
            ->andWhere('b.status = :status')->setParameter('status', 'cancelledByReferenceman')
            ->orderBy('serie')
            ->getQuery()->execute();

        $seriesChoices['- Выберите серию -'] = '';
        foreach ($series as $serie) {
            $seriesChoices[$serie['serie']] = $serie['serie'];
        }

        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'choices'           => $seriesChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);

        $form = $fb->getForm();
        $form->handleRequest($request);

        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->leftJoin('b.operator', 'o')->addSelect('o')
            ->leftJoin('b.reference_type', 'rt')->addSelect('rt')
            ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('b.referenceman_applied is not null')
            ->andWhere('b.status = :status')->setParameter('status', 'cancelledByReferenceman')
        ;

        if ($form->isValid()) {
            $data = $form->get('legal_entity')->getData();
            if ($data) {
                $qb->andWhere('b.legal_entity = :legal_entity')->setParameter('legal_entity', $data);
            }

            $data = $form->get('reference_type')->getData();
            if ($data) {
                $qb->andWhere('b.reference_type = :reference_type')->setParameter('reference_type', $data);
            }

            $data = $form->get('serie')->getData();
            if ($data) {
                $qb->andWhere('b.serie = :serie')->setParameter('serie', $data);
            }
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Referenceman:cancelled_blanks.html.twig', [
            'pagerfanta'  => $pagerfanta,
            'filter_form' => $form->createView(),
            'list_fields' => [
                ['legalEntityShortName', 'min_col text-left'],
                ['reference_type', 'min_col text-left'],
                ['serie', 'min_col text-left'],
                ['number', 'min_col text-right'],
            ],
        ]);
    }

    /** @Route("/operator-cancelled/", name="referenceman_blanks_operator_cancelled") */
    public function referencemanBlanksOperatorCancelledAction(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('last_name', TextType::class, [
            'label'    => 'Фамилия',
            'required' => false,
        ]);
        $fb->add('first_name', TextType::class, [
            'label'    => 'Имя',
            'required' => false,
        ]);
        $fb->add('patronymic', TextType::class, [
            'label'    => 'Отчество',
            'required' => false,
        ]);
        $form = $fb->getForm();

        $qb = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
            ->addSelect('count(ob.id) as amount')
            ->leftJoin('u.operator_blanks', 'ob', 'with', 'ob.status = :status')
            ->setParameter('status', 'cancelledByOperator')
            ->andWhere('u.active = :active')->setParameter('active', true)
            ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_OPERATOR%')
            ->groupBy('u.id')
            ->addOrderBy('u.last_name')
            ->addOrderBy('u.first_name')
            ->addOrderBy('u.patronymic')
            ->having('amount > 0')
        ;

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->get('last_name')->getData();
            if ($data) {
                $qb->andWhere('u.last_name LIKE :last_name')->setParameter('last_name', '%'.$data.'%');
            }

            $data = $form->get('first_name')->getData();
            if ($data) {
                $qb->andWhere('u.first_name LIKE :first_name')->setParameter('first_name', '%'.$data.'%');
            }

            $data = $form->get('patronymic')->getData();
            if ($data) {
                $qb->andWhere('u.patronymic LIKE :patronymic')->setParameter('patronymic', '%'.$data.'%');
            }
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Referenceman:cancelled_operator_blanks.html.twig', [
            'pagerfanta'  => $pagerfanta,
            'filter_form' => $form->createView(),
            'list_fields' => [
                'last_name',
                'first_name',
                'patronymic',
                ['amount', 'min_col text-right'],
            ],
        ]);
    }

    /** @Route("/referenceman-cancelled/undo-{id}/",
     *      name="referenceman_blanks_undo_referenceman_cancelled") */
    public function referencemanBlanksUndoReferencemanCancelledAction($id)
    {
        /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
        $blank = $this->em->find('CommonBundle:Blank', (int)$id);
        if (!$blank) {
            throw $this->createNotFoundException();
        }

        $oldStatus = $blank->getStatus();
        $blank->setStatus('acceptedByReferenceman');
        $blank->setReferencemanApplied(new \DateTime());
        $this->em->persist($blank);

        $user = $this->getUser(); /** @var $user \KreaLab\CommonBundle\Entity\User */
        $user->addReferencemanInterval(
            $blank->getLegalEntity(),
            $blank->getReferenceType(),
            $blank->getSerie(),
            $blank->getNumber(),
            1,
            $blank->getLeadingZeros()
        );
        $this->em->persist($user);

        $lifeLog = new BlankLifeLog();
        $lifeLog->setBlank($blank);
        $lifeLog->setOperationStatus($lifeLog::RR_CANCELED_NOT_FOUND_BLANK);
        $lifeLog->setEnvelopeId(null);
        $lifeLog->setEnvelopeType(null);

        $lifeLog->setStartStatus($oldStatus);
        $lifeLog->setEndStatus($blank->getStatus());

        $lifeLog->setStartUser($user);
        $lifeLog->setEndUser($user);

        $this->em->persist($lifeLog);

        $this->em->flush();

        $this->addFlash('success', 'Вернули бланк себе');
        return $this->redirectToRoute('referenceman_blanks_referenceman_cancelled');
    }

    /** @Route("/operator-cancelled/view-desc-{id}/",
     *  name="referenceman_blanks__operator_cancelled__view_desc") */
    public function referencemanBlanksOperatorCancelledViewDescAction($id)
    {
        $blank = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('b.status = :status')->setParameter('status', 'cancelledByOperator')
            ->andWhere('b.operator IS NOT NULL')
            ->andWhere('b.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        if (!$blank) {
            throw $this->createNotFoundException();
        }

        return $this->render('AppBundle:Referenceman:operator_cancelled__view_desc.html.twig', [
            'blank' => $blank,
        ]);
    }

    /** @Route("/operator-cancelled/view-{id}/",
     *  name="referenceman_blanks_view_operator_cancelled") */
    public function referencemanBlanksViewOperatorCancelledAction(Request $request, $id)
    {
        $blanks = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->leftJoin('b.operator', 'o')->addSelect('o')
            ->andWhere('o.id = :id')->setParameter('id', $id)
            ->andWhere('o.active = :active')->setParameter('active', true)
            ->andWhere('o.roles LIKE :role')->setParameter('role', '%ROLE_OPERATOR%')
            ->andWhere('b.status = :status')->setParameter('status', 'cancelledByOperator')
            ->getQuery()->execute();

        $blankChoices = [];
        foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $blankChoices[$blank->getId()] = $blank->getId();
        }

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('blanks', ChoiceType::class, [
            'choices_as_values' => true,
            'multiple'          => true,
            'expanded'          => true,
            'choices'           => $blankChoices,
        ]);
        $fb->add('save', SubmitType::class, [
            'label' => 'Подтвердить',
            'attr'  => ['class' => 'btn btn-success pull-right'],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $session = $this->get('session');
            $session->set('cancelled_operator_blanks', $form->get('blanks')->getData());
            return $this->redirectToRoute('referenceman_blanks__operator_referenceman_box__select');
        }

        return $this->render('AppBundle:Referenceman:view_operator_cancelled.html.twig', [
            'form'   => $form->createView(),
            'blanks' => $blanks,
        ]);
    }

    /** @Route("/operator-envelopes/send-{id}/",
     *      name="referenceman_blanks_send_operator_envelope") */
    public function referencemanBlanksSendOperatorEnvelopeAction(Request $request, $id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
        $envelope = $this->em->getRepository('CommonBundle:BlankOperatorEnvelope')->createQueryBuilder('boe')
            ->andWhere('boe.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('boe.operator_applied is null')
            ->andWhere('boe.operator is null')
            ->andWhere('boe.id = :id')->setParameter('id', $id)
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$envelope) {
            throw $this->createNotFoundException();
        }

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('operator', EntityType::class, [
            'label'         => 'Оператор',
            'placeholder'   => ' - Выберите оператора - ',
            'constraints'   => new Assert\NotBlank(),
            'class'         => 'CommonBundle:User',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.active = :active')->setParameter('active', true)
                    ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_OPERATOR%')
                    ->addOrderBy('u.last_name')
                    ->addOrderBy('u.first_name')
                    ->addOrderBy('u.patronymic')
                ;
            },
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $operator = $form->get('operator')->getData();

            $envelope->setOperator($operator);
            $this->em->persist($envelope);
            $this->em->flush();

            $blanks = $this->em->getRepository('CommonBundle:Blank')->findBy([
                'operator_envelope' => $envelope,
                'status'            => 'inEnvelopeForOperator',
                'operator_applied'  => null,
            ]);

            foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                $oldStatus = $blank->getStatus();
                $blank->setStatus('appointedToOperator');
                $blank->setOperator($operator);
                $this->em->persist($blank);

                $lifeLog = new BlankLifeLog();

                $status = $oldStatus == 'inEnvelopeForOperator'
                    ? $lifeLog::RO_ASSIGN_ENVELOP_TO_OPERATOR
                    : $lifeLog::RO_REPEATED_ASSIGN_ENVELOP_TO_OPERATOR;

                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($status);
                $lifeLog->setEnvelopeId($envelope->getId());
                $lifeLog->setEnvelopeType('blank_operator_envelope');

                $lifeLog->setStartStatus($oldStatus);
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($this->getUser());
                $lifeLog->setEndUser($operator);

                $this->em->persist($lifeLog);
            }

            $this->em->flush();

            $this->addFlash('success', 'Передали');
            return $this->redirectToRoute('referenceman_blanks_operator_envelopes');
        }

        return $this->render('AppBundle:Referenceman:send_envelope_to_operator.html.twig', [
            'form'     => $form->createView(),
            'envelope' => $envelope,
        ]);
    }

    /** @Route("/operator-envelopes/add-{legalEntity}-{referenceType}∈{serie}/",
     * name="referenceman_blanks_add_operator_envelope_reference_type_serie")
     * @Route("/operator-envelopes/add-{legalEntity}-{referenceType}/",
     * name="referenceman_blanks_add_operator_envelope_reference_type") */
    public function referencemanBlanksAddOperatorEnvelopeAction(
        Request $request,
        $legalEntity,
        $referenceType,
        $serie = null
    ) {
        $maxResult     = 1000;
        $legalEntity   = $this->em->getRepository('CommonBundle:LegalEntity')->find($legalEntity);
        $referenceType = $this->em->getRepository('CommonBundle:ReferenceType')->find($referenceType);
        $serie         = trim($serie);

        if (!$referenceType) {
            throw $this->createNotFoundException();
        }

        if ($referenceType->getIsSerie() && !$serie) {
            throw $this->createNotFoundException();
        }

        $fb = $this->get('form.factory')->createNamedBuilder('', FormType::class, null, [
            'translation_domain' => false,
            'csrf_protection'    => false,
        ]);
        $fb->add('stamp', CheckboxType::class, [
            'label'    => 'С печатью',
            'required' => false,
            'data'     => true,
        ]);
        $fb->add('blank_operator_envelope', EntityType::class, [
            'label'         => 'Конверт',
            'placeholder'   => ' - Новый конверт - ',
            'class'         => 'CommonBundle:BlankOperatorEnvelope',
            'required'      => false,
            'query_builder' => function (EntityRepository $er) use ($legalEntity, $referenceType, $serie) {
                return $er->createQueryBuilder('boe')
                    ->andWhere('boe.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                    ->andWhere('boe.legal_entity = :legal_entity')->setParameter('legal_entity', $legalEntity)
                    ->andWhere('boe.operator IS NULL')
                    ->andWhere('boe.serie = :serie')->setParameter('serie', $serie)
                ;
            },
        ]);
        $fb->add('first_num', TextType::class, [
            'label'       => 'Номер первого бланка',
            'attr'        => ['min' => 1],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\GreaterThan(0),
            ],
        ]);
        $fb->add('amount', IntegerType::class, [
            'label'       => 'Количество',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('operator', EntityType::class, [
            'label'         => 'Оператор',
            'placeholder'   => ' - Выберите оператора - ',
            'class'         => 'CommonBundle:User',
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.active = :active')->setParameter('active', true)
                    ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_OPERATOR%')
                    ->addOrderBy('u.last_name')
                    ->addOrderBy('u.first_name')
                    ->addOrderBy('u.patronymic')
                    ;
            },
        ]);
        $fb->setMethod('get');
        $form = $fb->getForm();

        $form2        = null;
        $blanks       = [];
        $blankChoices = [];

        if ($request->isMethod('post')) {
            $form->submit($request->query->all());
        } else {
            $form->handleRequest($request);
        }

        if ($form->isValid()) {
            $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
                ->andWhere('b.referenceman_applied is not null')
                ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                ->andWhere('b.reference_type = :reference_type')
                ->setParameter('reference_type', $referenceType)
                ->andWhere('b.legal_entity = :legal_entity')
                ->setParameter('legal_entity', $legalEntity)
                ->andWhere('b.number >= :first_num')->setParameter('first_num', $form->get('first_num')->getData())
                ->andWhere('b.operator_envelope is null')
                ->andWhere('b.leading_zeros = :leading_zeros')
                ->setParameter('leading_zeros', strlen($form->get('first_num')->getData()))
                ->orderBy('b.number')
                ->setMaxResults($form->get('amount')->getData())
            ;

            if ($referenceType->getIsSerie()) {
                $qb->andWhere('b.serie = :serie')->setParameter('serie', $serie);
            }

            $blanks = $qb->getQuery()->execute();
            foreach ($blanks as &$blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                $blankChoices[$blank->getId()] = $blank->getId();
            }

            /**
             * ФОРМА БЛАНКОВ
             */
            $fb2 = $this->get('form.factory')->createNamedBuilder('form2', FormType::class, null, [
                'translation_domain' => false,
                'csrf_protection'    => false,
            ]);
            $fb2->add('blanks', ChoiceType::class, [
                'choices_as_values' => true,
                'multiple'          => true,
                'expanded'          => true,
                'choices'           => $blankChoices,
            ]);
            $fb2->add('save', SubmitType::class, [
                'label' => 'Сформировать конверт',
                'attr'  => ['class' => 'btn btn-success pull-right'],
            ]);
            $form2 = $fb2->getForm();

            $form2->handleRequest($request);
            if ($form2->isValid()) {
                $this->em->beginTransaction();
                $this->em->getConnection()->setTransactionIsolation(4);

                $chosenBlanks = $form2->get('blanks')->getData();
                $blankIds     = array_values($chosenBlanks);

                $blanksValid = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                    ->andWhere('b.id IN (:ids)')->setParameter('ids', $blankIds)
                    ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
                    ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                    ->andWhere('b.referenceman_applied is not null')
                    ->andWhere('b.number >= :first_num')
                    ->andWhere('b.reference_type = :reference_type')
                    ->setParameter('reference_type', $referenceType)
                    ->andWhere('b.legal_entity = :legal_entity')
                    ->setParameter('legal_entity', $legalEntity)
                    ->setParameter('first_num', $request->get('first_num', 1))
                    ->andWhere('b.leading_zeros = :leading_zeros')
                    ->setParameter('leading_zeros', strlen($form->get('first_num')->getData()))
                    ->orderBy('b.number')
                    ->setMaxResults($request->get('amount', $maxResult))
                    ->getQuery()->getResult();

                if (!empty($blanksValid)) { /** @var $referenceman \KreaLab\CommonBundle\Entity\User */
                    $referenceman = $this->getUser();

                    $envelope = $form->get('blank_operator_envelope')->getData();
                    if (!$envelope) {
                        $envelope = new BlankOperatorEnvelope();
                        $envelope->setReferenceman($referenceman);
                        $envelope->setReferenceType($referenceType);
                        $envelope->setLegalEntity($legalEntity);
                        $envelope->setSerie($serie);
                        /** @var $firstBlank \KreaLab\CommonBundle\Entity\Blank */
                        $firstBlank = $blanksValid[0];
                        $envelope->setLeadingZeros($firstBlank->getLeadingZeros());
                        $envelope->setFirstNum($firstBlank->getNumber());
                        $envelope->setStamp($request->get('stamp', false));
                    } else {
                        $envelope = $this->em->find('CommonBundle:BlankOperatorEnvelope', $envelope);
                    }

                    if ($form->get('operator')->getData()) {
                        $envelope->setOperator($form->get('operator')->getData());
                        foreach ($envelope->getBlanks() as $blank) {
                            $blank->setOperator($form->get('operator')->getData());
                            $this->em->persist($blank);
                        }
                    }

                    $envelope->setAmount($envelope->getAmount() + count($blanksValid));
                    $this->em->persist($envelope);
                    $this->em->flush();

                    $cnt = 0;
                    foreach ($blanksValid as &$blankId) {
                        $blank = $this->em->find('CommonBundle:Blank', $blankId);

                        $oldStatus = $blank->getStatus();
                        if ($form->get('operator')->getData()) {
                            $blank->setStatus('appointedToOperator');
                            $blank->setOperator($form->get('operator')->getData());
                        } else {
                            $blank->setStatus('inEnvelopeForOperator');
                        }

                        $blank->setOperatorEnvelope($envelope);
                        $this->em->persist($blank);

                        $lifeLog = new BlankLifeLog();
                        $lifeLog->setBlank($blank);
                        $lifeLog->setOperationStatus($lifeLog::RR_CREATE_ENVELOP_REFERENCE_TO_OPERATOR);
                        $lifeLog->setEnvelopeId($envelope->getId());
                        $lifeLog->setEnvelopeType('blank_operator_envelope');

                        $lifeLog->setStartStatus($oldStatus);
                        $lifeLog->setEndStatus($blank->getStatus());

                        $lifeLog->setStartUser($referenceman);
                        $lifeLog->setEndUser($referenceman);

                        $this->em->persist($lifeLog);

                        $referenceman->removeReferencemanInterval(
                            $blank->getLegalEntity(),
                            $blank->getReferenceType(),
                            $blank->getSerie(),
                            $blank->getNumber(),
                            1,
                            $blank->getLeadingZeros()
                        );

                        $envelope->addInterval(
                            $blank->getLegalEntity(),
                            $blank->getReferenceType(),
                            $blank->getSerie(),
                            $blank->getNumber(),
                            1,
                            $blank->getLeadingZeros()
                        );

                        if ($cnt % 100 == 0) {
                            $this->em->flush();

                            $this->em->detach($blank);
                            $this->em->detach($lifeLog);
                        }

                        ++$cnt;
                    }

                    $this->em->persist($envelope);
                    $this->em->persist($referenceman);
                    $this->em->flush();
                    $this->em->clear();
                    $this->em->commit();

                    $this->addFlash('success', 'Сформировали');

                    return $this->redirectToRoute(
                        'referenceman_blanks__referenceman_operator_envelope__view_after_creating',
                        ['id' => $envelope->getId()]
                    );
                } else {
                    $this->addFlash('danger', 'Не выбраны бланки');
                }
            }
        }

        $info                  = [];
        $info['amount']        = 0;
        $intervalsArr          = [];
        $referenceman          = $this->getUser(); /** @var $referenceman \KreaLab\CommonBundle\Entity\User */
        $referencemanIntervals = $referenceman->getReferencemanIntervals();
        $intervals             = $referencemanIntervals[$legalEntity->getId()][$referenceType->getId()][$serie];

        foreach ($intervals as $leadingZero => $curIntervals) {
            foreach ($curIntervals as $int) {
                $int[0]         = str_pad($int[0], $leadingZero, '0', STR_PAD_LEFT);
                $int[1]         = str_pad($int[1], $leadingZero, '0', STR_PAD_LEFT);
                $intervalsArr[] = ($int[0] == $int[1]) ? '['.$int[0].']' : '['.$int[0].', '.$int[1].']';

                $intervalsArr['interval'] = ($int[0] == $int[1]) ? '['.$int[0].']' : '['.$int[0].', '.$int[1].']';
                $intervalsArr['amount']   = $int[1] - $int[0] + 1;
                $info['intervals'][]      = $intervalsArr;
                $info['amount']          += $intervalsArr['amount'];
            }
        }

        $envelopes = $this->em->getRepository('CommonBundle:BlankOperatorEnvelope')->createQueryBuilder('boe')
            ->andWhere('boe.legal_entity = :legal_entity')->setParameter('legal_entity', $legalEntity)
            ->andWhere('boe.operator IS NULL')
            ->andWhere('boe.serie = :serie')->setParameter('serie', $serie)
            ->getQuery()->getResult();

        $envelopesArr = [];

        foreach ($envelopes as $envelope) { /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
            $envelopesArr[$envelope->getId()] = $envelope->getStamp();
        }

        return $this->render('AppBundle:Referenceman:add_operator_envelope.html.twig', [
            'form'          => $form->createView(),
            'form2'         => $form2 ? $form2->createView() : null,
            'blanks'        => $blanks,
            'legalEntity'   => $legalEntity,
            'referenceType' => $referenceType,
            'serie'         => $serie,
            'info'          => $info,
            'envelopes'     => $envelopesArr,
        ]);
    }

    /**
     * @Route("/not-in-operator-envelopes/", name="referenceman_blanks_not_in_operator_envelopes")
     */
    public function referencemanBlanksNotInOperatorEnvelopesAction(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);

        $fb->add('legal_entity', EntityType::class, [
            'label'        => 'Юридическое лицо',
            'class'        => 'CommonBundle:LegalEntity',
            'placeholder'  => ' - Выберите юридическое лицо - ',
            'choice_label' => 'nameAndShortName',
            'required'     => false,
        ]);

        $fb->add('reference_type', EntityType::class, [
            'label'         => 'Тип бланка',
            'class'         => 'CommonBundle:ReferenceType',
            'placeholder'   => ' - Выберите тип бланка - ',
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('rt')
                    ->innerJoin('rt.blanks', 'b', 'WITH', 'b.status = :status')
                    ->setParameter('status', 'acceptedByReferenceman')
                    ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                    ;
            },
        ]);

        $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT(b.serie) AS serie')
            ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
            ->andWhere('b.serie != :serie')->setParameter('serie', '')
            ->orderBy('serie')
            ->getQuery()->execute();

        $seriesChoices['- Выберите серию -'] = '';
        foreach ($series as $serie) {
            $seriesChoices[$serie['serie']] = $serie['serie'];
        }

        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'choices'           => $seriesChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);

        $intervals = $this->getUser()->getReferencemanIntervals();

        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->get('legal_entity')->getData();
            if ($data) {
                $filteredIntervals                 = [];
                $filteredIntervals[$data->getId()] = $intervals[$data->getId()];
                $intervals                         = $filteredIntervals;
            }

            $data = $form->get('reference_type')->getData();
            if ($data) {
                $filteredIntervals = [];
                foreach ($intervals as $legalEntityId => $interval) {
                    if (isset($intervals[$legalEntityId][$data->getId()])) {
                        $filteredIntervals[$legalEntityId][$data->getId()]
                            = $intervals[$legalEntityId][$data->getId()];
                    }
                }

                $intervals = $filteredIntervals;
            }

            $data = (string)$form->get('serie')->getData();
            if ($data) {
                $filteredIntervals = [];
                foreach ($intervals as $legalEntityId => $refTypesIntervals) {
                    foreach ($refTypesIntervals as $refTypeId => $interval) {
                        if (isset($intervals[$legalEntityId][$refTypeId][$data])) {
                            $filteredIntervals[$legalEntityId][$refTypeId][$data]
                                = $intervals[$legalEntityId][$refTypeId][$data];
                        }
                    }
                }

                $intervals = $filteredIntervals;
            }
        }

        $referenceTypes = [];
        foreach ($this->em->getRepository('CommonBundle:ReferenceType')->findAll() as $referenceType) {
            $referenceTypes[$referenceType->getId()] = $referenceType;
        }

        $legalEntities = [];
        foreach ($this->em->getRepository('CommonBundle:LegalEntity')->findAll() as $legalEntity) {
            $legalEntities[$legalEntity->getId()] = $legalEntity;
        }

        return $this->render('AppBundle:Referenceman:on_hands.html.twig', [
            'referenceman_invervals' => $intervals,
            'reference_types'        => $referenceTypes,
            'legal_entities'         => $legalEntities,
            'filter_form'            => $form->createView(),
        ]);
    }

    /**
     * @Route("/not-in-operator-envelopes/view-list-e{lEntityId}-r{refTypeId}-s{serie}/",
     *      name="referenceman_blanks_not_in_operator_envelopes__view_list")
     */
    public function referencemanBlanksNotInOperatorEnvelopesViewListAction(
        Request $request,
        $lEntityId,
        $refTypeId,
        $serie = ''
    ) {
        if ($serie == '-') {
            $serie = '';
        }

        $legalEntity = $this->em->find('CommonBundle:LegalEntity', $lEntityId);
        if (!$legalEntity) {
            throw $this->createNotFoundException('Legal entity is not found.');
        }

        $referenceType = $this->em->find('CommonBundle:ReferenceType', $refTypeId);
        if (!$referenceType) {
            throw $this->createNotFoundException('Reference type is not found.');
        }

        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
            ->andWhere('b.reference_type = :reference_type')->setParameter('reference_type', $referenceType)
            ->andWhere('b.legal_entity = :legal_entity')->setParameter('legal_entity', $legalEntity)
        ;

        if (empty($serie)) {
            $qb->andWhere('b.serie = :serie')->setParameter('serie', '');
        } else {
            $qb->andWhere('b.serie = :serie')->setParameter('serie', $serie);
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Referenceman:on_hands_view_list.html.twig', [
            'pagerfanta'  => $pagerfanta,
        ]);
    }

    /**
     * @Route("/not-in-operator-envelopes/view-{lEntityId}-{refTypeId}∈{serie}/",
     *      name="referenceman_blanks_not_in_operator_envelopes__view_serie")
     * @Route("/not-in-operator-envelopes/view-{lEntityId}-{refTypeId}/",
     *      name="referenceman_blanks_not_in_operator_envelopes__view")
     */
    public function referencemanBlanksNotInOperatorEnvelopesViewAction(
        $lEntityId,
        $refTypeId,
        $serie = '-'
    ) {
        $legalEntity = $this->em->find('CommonBundle:LegalEntity', $lEntityId);
        if (!$legalEntity) {
            throw $this->createNotFoundException('Legal entity is not found.');
        }

        $referenceType = $this->em->find('CommonBundle:ReferenceType', $refTypeId);
        if (!$referenceType) {
            throw $this->createNotFoundException('Reference type is not found.');
        }

        $referenceman = $this->getUser(); /** @var $referenceman \KreaLab\CommonBundle\Entity\User */
        $intervals    = $referenceman->getReferencemanIntervals();
        $intervals    = $intervals[$legalEntity->getId()][$referenceType->getId()][$serie];

        return $this->render('AppBundle:Referenceman:on_hands_view.html.twig', [
            'intervals'     => $intervals,
            'legalEntity'   => $legalEntity,
            'referenceType' => $referenceType,
            'serie'         => $serie,
        ]);
    }

    /**
     * @Route("/referenceman-blanks/delete-envelope-of-transfered-blanks-to-stockmn-or-refman-{envelopeId}-{userType}/",
     *     name="delete_envelope_of_transfered_blanks_to_stockman_or_referenceman")
     */
    public function referencemanBlanksDeleteEnvelopeOfTransferedBlanksToStockmanOrReferenceman(
        $envelopeId,
        $userType
    ) {
        $referenceman = $this->getUser(); /** @var $referenceman \KreaLab\CommonBundle\Entity\User */

        if ($userType == 'stockman') {
            $envelope = $this->em->getRepository('CommonBundle:BlankStockmanEnvelope')->findOneBy([
                'id'           => $envelopeId,
                'referenceman' => $referenceman,
            ]);

            if (!$envelope) {
                throw $this->createNotFoundException();
            }

            $this->em->beginTransaction();

            $cnt = 0;
            foreach ($envelope->getBlanks() as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                if ($blank->getStatus() == 'appointedToStockman') {
                    $stockman  = $envelope->getStockman();
                    $oldStatus = $blank->getStatus();
                    $blank->setStatus('acceptedByReferenceman');
                    $blank->setStockmanEnvelope(null);
                    $oldReferenceman = $blank->getOldReferenceman();
                    $blank->setReferenceman($oldReferenceman);
                    $blank->setOldReferenceman(null);
                    $blank->setReferencemanApplied(new \DateTime());
                    $blank->setStockmanEnvelope(null);
                    $blank->setReferencemanReferencemanEnvelope(null);

                    $this->em->persist($blank);

                    $referenceman->addReferencemanInterval(
                        $blank->getLegalEntity(),
                        $blank->getReferenceType(),
                        $blank->getSerie(),
                        $blank->getNumber(),
                        1,
                        $blank->getLeadingZeros()
                    );
                    $this->em->persist($referenceman);

                    $lifeLog = new BlankLifeLog();
                    $lifeLog->setBlank($blank);
                    $lifeLog->setOperationStatus($lifeLog::SR_CANCELED_REVERT_BLANK_TO_STOCK);
                    $lifeLog->setEnvelopeId($envelope->getId());
                    $lifeLog->setEnvelopeType('blank_stockman_envelope');

                    $lifeLog->setStartStatus($oldStatus);
                    $lifeLog->setEndStatus($blank->getStatus());

                    $lifeLog->setStartUser($stockman);
                    $lifeLog->setEndUser($this->getUser());

                    $this->em->persist($lifeLog);

                    if ($cnt % 100 == 0) {
                        $this->em->flush();
                        $this->em->detach($lifeLog);
                    }

                    ++$cnt;
                }
            }

            $this->em->remove($envelope);
            $this->em->flush();
            $this->em->clear();
            $this->em->commit();
        } elseif ($userType == 'referenceman') {
            $envelope = $this->em->getRepository('CommonBundle:BlankReferencemanReferencemanEnvelope')->findOneBy([
                'id'               => $envelopeId,
                'old_referenceman' => $referenceman,
            ]);

            if (!$envelope) {
                throw $this->createNotFoundException();
            }

            $this->em->beginTransaction();

            $cnt = 0;
            foreach ($envelope->getBlanks() as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                if ($blank->getStatus() == 'appointedToReferencemanFromReferenceman') {
                    $oldStatus        = $blank->getStatus();
                    $oldAssignedRefer = $envelope->getReferenceman();
                    $blank->setStatus('acceptedByReferenceman');
                    $blank->setReferencemanReferencemanEnvelope(null);
                    $oldReferenceman = $blank->getOldReferenceman();
                    $blank->setReferenceman($oldReferenceman);
                    $blank->setOldReferenceman(null);
                    $blank->setReferencemanApplied(new \DateTime());

                    $this->em->persist($blank);

                    $referenceman->addReferencemanInterval(
                        $blank->getLegalEntity(),
                        $blank->getReferenceType(),
                        $blank->getSerie(),
                        $blank->getNumber(),
                        1,
                        $blank->getLeadingZeros()
                    );
                    $this->em->persist($referenceman);

                    $lifeLog = new BlankLifeLog();
                    $lifeLog->setBlank($blank);
                    $lifeLog->setOperationStatus($lifeLog::RR_CANCELED_REVERT_BLANK_TO_REFER);
                    $lifeLog->setEnvelopeId($envelope->getId());
                    $lifeLog->setEnvelopeType('blank_stockman_envelope');

                    $lifeLog->setStartStatus($oldStatus);
                    $lifeLog->setEndStatus($blank->getStatus());

                    $lifeLog->setStartUser($oldAssignedRefer);
                    $lifeLog->setEndUser($this->getUser());

                    $this->em->persist($lifeLog);

                    if ($cnt % 100 == 0) {
                        $this->em->flush();
                        $this->em->detach($lifeLog);
                    }

                    ++$cnt;
                }
            }

            $this->em->remove($envelope);
            $this->em->flush();
            $this->em->clear();
            $this->em->commit();
        }

        return $this->redirectToRoute('referenceman_blanks_envelopes_of_transfered_blanks_to_stockman_or_referenceman');
    }

    /**
     * @Route("/referenceman-blanks/envelope-of-transfered-blanks-to-sman-or-refman-intervals-{envelopeId}-{userType}/",
     *     name="envelope_of_transfered_blanks_to_stockman_or_referenceman__intervals")
     */
    public function referencemanBlanksEnvelopeOfTransferedBlanksToStockmanOrReferencemanIntervals(
        $envelopeId,
        $userType
    ) {
        $referenceman = $this->getUser(); /** @var $referenceman \KreaLab\CommonBundle\Entity\User */

        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankStockmanEnvelope */
        $envelope = null;
        if ($userType == 'stockman') {
            $envelope = $this->em->getRepository('CommonBundle:BlankStockmanEnvelope')->findOneBy([
                'id'           => $envelopeId,
                'referenceman' => $referenceman,
            ]);

            if (!$envelope) {
                throw $this->createNotFoundException();
            }
        } elseif ($userType == 'referenceman') {
            $envelope = $this->em->getRepository('CommonBundle:BlankReferencemanReferencemanEnvelope')->findOneBy([
                'id'               => $envelopeId,
                'old_referenceman' => $referenceman,
            ]);

            if (!$envelope) {
                throw $this->createNotFoundException();
            }
        }

        if (empty($envelope->getSerie())) {
            $serie = '-';
        } else {
            $serie = $envelope->getSerie();
        }

        $intervals     = $envelope->getIntervals();
        $legalEntityId = $envelope->getLegalEntity()->getId();
        $refTypeId     = $envelope->getReferenceType()->getId();
        $intervals     = $intervals[$legalEntityId][$refTypeId][$serie];

        return $this->render(
            'AppBundle:Referenceman:envelope_of_transfered_blanks_to_stockman_or_referenceman_intervals.html.twig',
            [
                'envelope'  => $envelope,
                'intervals' => $intervals,
            ]
        );
    }

    /**
     * @Route("/referenceman-blanks/envelope-of-transfered-blanks-to-stockman-or-refman-list-{envelopeId}-{userType}/",
     *     name="envelope_of_transfered_blanks_to_stockman_or_referenceman__list")
     */
    public function referencemanBlanksEnvelopeOfTransferedBlanksToStockmanOrReferencemanList($envelopeId, $userType)
    {
        /** @var $referenceman \KreaLab\CommonBundle\Entity\User */
        $referenceman = $this->getUser();

        $envelope = null;
        if ($userType == 'stockman') {
            $envelope = $this->em->getRepository('CommonBundle:BlankStockmanEnvelope')->findOneBy([
                'id'           => $envelopeId,
                'referenceman' => $referenceman,
            ]);

            if (!$envelope) {
                throw $this->createNotFoundException();
            }
        } elseif ($userType == 'referenceman') {
            $envelope = $this->em->getRepository('CommonBundle:BlankReferencemanReferencemanEnvelope')->findOneBy([
                'id'               => $envelopeId,
                'old_referenceman' => $referenceman,
            ]);

            if (!$envelope) {
                throw $this->createNotFoundException();
            }
        }

        return $this->render(
            'AppBundle:Referenceman:envelope_of_transfered_blanks_to_stockman_or_referenceman_list.html.twig',
            ['envelope' => $envelope]
        );
    }

    /**
     * @Route("/envelopes-of-transfered-blanks-to-stockman-or-referenceman/",
     *     name="referenceman_blanks_envelopes_of_transfered_blanks_to_stockman_or_referenceman")
     */
    public function referencemanBlanksEnvelopesOfTransferedBlanksToStockmanOrReferenceman()
    {
        $refRefEnvelopes = $this->em
            ->getRepository('CommonBundle:BlankReferencemanReferencemanEnvelope')
            ->createQueryBuilder('brre')
            ->leftJoin('brre.blanks', 'b', 'WITH', 'b.status = :status')
            ->setParameter('status', 'appointedToReferencemanFromReferenceman')
            ->addSelect('b')
            ->andWhere('brre.referenceman_applied IS NULL')
            ->andWhere('brre.old_referenceman = :old_referenceman')->setParameter('old_referenceman', $this->getUser())
            ->getQuery()->execute();
        foreach ($refRefEnvelopes as &$envelope) {
            /** @var $envelope \KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope */

            foreach ($envelope->getBlanks() as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                $envelope->addInterval(
                    $blank->getLegalEntity(),
                    $blank->getReferenceType(),
                    $blank->getSerie(),
                    $blank->getNumber(),
                    1,
                    $blank->getLeadingZeros()
                );
            }
        }

        $referencemanStockmanEnvelopes = $this->em->getRepository('CommonBundle:BlankStockmanEnvelope')
            ->createQueryBuilder('bse')
            ->leftJoin('bse.blanks', 'b', 'WITH', 'b.status = :status')
            ->setParameter('status', 'appointedToStockman')
            ->addSelect('b')
            ->andWhere('bse.stockman_applied IS NULL')
            ->andWhere('bse.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->getQuery()->execute();
        foreach ($referencemanStockmanEnvelopes as &$envelope) {
            /** @var $envelope \KreaLab\CommonBundle\Entity\BlankStockmanEnvelope */

            foreach ($envelope->getBlanks() as $blank) {
                $envelope->addInterval(
                    $blank->getLegalEntity(),
                    $blank->getReferenceType(),
                    $blank->getSerie(),
                    $blank->getNumber(),
                    1,
                    $blank->getLeadingZeros()
                );
            }
        }

        $envelopes = array_merge($refRefEnvelopes, $referencemanStockmanEnvelopes);

        return $this->render(
            'AppBundle:Referenceman:envelopes_of_transfered_blanks_to_stockman_or_referenceman.html.twig',
            ['envelopes' => $envelopes]
        );
    }

    /** @Route("/transfer-blanks-to-stockman-or-referenceman/",
     *     name="referenceman_blanks_transfer_blanks_to_stockman_or_referenceman") */
    public function referencemanBlanksTransferBlanksToStockmanOrReferenceman(Request $request)
    {
        $lEntityId = $request->get('lEntityId');
        $refTypeId = $request->get('refTypeId');
        $amount    = $request->get('amount', 1);
        $serieIn   = $request->get('serieIn');

        $maxResult = 1000;
        $data      = [];
        if ($lEntityId) {
            $legalEntity = $this->em->getRepository('CommonBundle:LegalEntity')->find($lEntityId);
            if (!$legalEntity) {
                throw $this->createNotFoundException('Legal entity not found');
            }

            $data['legal_entity'] = $legalEntity;
        }

        if ($refTypeId) {
            $referenceType = $this->em->getRepository('CommonBundle:ReferenceType')->find($refTypeId);
            if (!$referenceType) {
                throw $this->createNotFoundException('Reference type not found');
            }

            $data['reference_type'] = $referenceType;
        }

        $serie         = null;
        $seriesChoices = [];
        if (!empty($serieIn) and $serieIn != '-') {
            $serie = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->select('DISTINCT(b.serie) AS serie')
                ->andWhere('b.serie = :serie')->setParameter('serie', $serieIn)
                ->getQuery()->getSingleScalarResult();

            if (!$serie) {
                throw $this->createNotFoundException('Serie not found');
            }

            $data['serie']         = $serie;
            $seriesChoices[$serie] = $serie;
        } else {
            $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->select('DISTINCT(b.serie) AS serie')
                ->andWhere('b.serie IS NOT NULL')
                ->orderBy('serie')
                ->getQuery()->execute();
            foreach ($series as $serie) {
                $seriesChoices[$serie['serie']] = $serie['serie'];
            }
        }

        $fb = $this->get('form.factory')->createNamedBuilder('', FormType::class, null, [
            'translation_domain' => false,
            'csrf_protection'    => false,
        ]);
        $fb->add('legal_entity', EntityType::class, [
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'choice_label'  => 'nameAndShortName',
            'query_builder' => function (EntityRepository $er) use ($lEntityId) {
                if ($lEntityId) {
                    return $er->createQueryBuilder('le')
                        ->andWhere('le.id = :id')->setParameter('id', $lEntityId);
                } else {
                    return $er->createQueryBuilder('le')
                        ->andWhere('le.active = :active')->setParameter('active', true);
                }
            },
        ]);
        $fb->add('stockman_or_referenceman', EntityType::class, [
            'label'         => 'Кладовщик или справковед',
            'placeholder'   => ' - Выберите кладовщика или справковеда - ',
            'class'         => 'CommonBundle:User',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.id != :userId')->setParameter('userId', $this->getUser())
                    ->andWhere('u.active = :active')->setParameter('active', true)
                    ->andWhere('(u.roles LIKE :role_stockman OR u.roles LIKE :role_referenceman)')
                    ->setParameter('role_stockman', '%ROLE_STOCKMAN%')
                    ->setParameter('role_referenceman', '%ROLE_REFERENCEMAN%')
                    ->addOrderBy('u.last_name')
                    ->addOrderBy('u.first_name')
                    ->addOrderBy('u.patronymic')
                    ;
            },
        ]);
        $fb->add('reference_type', EntityType::class, [
            'label'         => 'Тип справки',
            'class'         => 'CommonBundle:ReferenceType',
            'placeholder'   => ' - Выберите тип справки - ',
            'query_builder' => function (EntityRepository $er) use ($refTypeId) {
                if ($refTypeId) {
                    return $er->createQueryBuilder('rt')
                        ->andWhere('rt.id = :id')->setParameter('id', $refTypeId);
                } else {
                    return $er->createQueryBuilder('rt');
                }
            },
        ]);
        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'placeholder'       => ' - Выберите серию - ',
            'choices'           => $seriesChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);
        $fb->add('first_num', TextType::class, [
            'label' => 'Номер первого бланка',
            'attr'  => ['min' => 1],
        ]);
        $fb->add('amount', IntegerType::class, [
            'label'       => 'Количество',
            'attr'        => ['min' => 1, 'max' => $maxResult],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\GreaterThan(0),
                new Assert\LessThanOrEqual($maxResult),
            ],
        ]);

        $fb->setMethod('get');
        $fb->setData($data);
        $form = $fb->getForm();
        $form->handleRequest($request);

        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
            ->andWhere('b.referenceman_applied is not null')
            ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('b.reference_type = :reference_type')
            ->setParameter('reference_type', $request->get('reference_type'))
            ->andWhere('b.legal_entity = :legal_entity')
            ->setParameter('legal_entity', $request->get('legal_entity'))
            ->andWhere('b.number >= :first_num')->setParameter('first_num', $request->get('first_num'))
            ->andWhere('b.operator_envelope is null')
            ->andWhere('b.leading_zeros = :leading_zeros')
            ->setParameter('leading_zeros', strlen($request->get('first_num')))
            ->setMaxResults($form->get('amount')->getData())
        ;

        if (!empty($form->get('reference_type')->getData())) {
            $referenceType = $this->em->getRepository('CommonBundle:ReferenceType')
                ->find($form->get('reference_type')->getData());

            if ($referenceType->getIsSerie()) {
                $qb->andWhere('b.serie = :serie')->setParameter('serie', $request->get('serie'));
            }
        }

        $blanks = $qb->getQuery()->execute();

        $blankChoices = [];
        foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $blankChoices[$blank->getId()] = $blank->getId();
        }

        $fb2 = $this->get('form.factory')->createNamedBuilder('form2', FormType::class, null, [
                'translation_domain' => false,
        ]);
        $fb2->add('blanks', ChoiceType::class, [
            'choices_as_values' => true,
            'multiple'          => true,
            'expanded'          => true,
            'choices'           => $blankChoices,
            'constraints'       => new Assert\NotBlank(['message' => 'Не выбрали бланки']),
        ]);
        $fb2->add('save', SubmitType::class, [
            'label' => 'Передать',
            'attr'  => ['class' => 'btn btn-success pull-right'],
        ]);

        $form2 = $fb2->getForm();
        $form2->handleRequest($request);

        if ($form2->isSubmitted() and empty($form2->get('blanks')->getData())) {
            $this->addFlash('danger', 'Выберите бланки');
            return $this->redirect($request->headers->get('referer'));
        }

        if ($form2->isValid()) {
            $this->em->beginTransaction();
            $referenceType = $this->em->find('CommonBundle:ReferenceType', $request->get('reference_type'));
            $legalEntity   = $this->em->find('CommonBundle:LegalEntity', $request->get('legal_entity'));
            $serie         = $request->get('serie');

            /** @var $stockmanOrReferenceman \KreaLab\CommonBundle\Entity\User */
            $stockmanOrReferenceman = $this->em->getRepository('CommonBundle:User')
                ->find($request->get('stockman_or_referenceman'));

            $blanks   = $form2->get('blanks')->getData();
            $blankIds = array_values($blanks);

            $blanksValid = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->andWhere('b.id IN (:ids)')->setParameter('ids', $blankIds)
                ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
                ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                ->setMaxResults($request->get('amount', $maxResult))
                ->getQuery()->getResult();

            if (!empty($blanksValid)) { /** @var $firstBlank \KreaLab\CommonBundle\Entity\Blank */
                $firstBlank = $blanksValid[0];
                if ($stockmanOrReferenceman->hasOneOfRoles('ROLE_REFERENCEMAN')) {
                    /** @var $newReferenceman \KreaLab\CommonBundle\Entity\User */
                    $newReferenceman = &$stockmanOrReferenceman;
                    $oldReferenceman = $this->getUser();
                    /** @var $oldReferenceman \KreaLab\CommonBundle\Entity\User */

                    $envelope = new BlankReferencemanReferencemanEnvelope();
                    $envelope->setReferenceman($newReferenceman);
                    $envelope->setOldReferenceman($oldReferenceman);
                    $envelope->setFirstNum($firstBlank->getNumber());
                    $envelope->setSerie($serie);
                    $envelope->setReferenceType($referenceType);
                    $envelope->setLegalEntity($legalEntity);
                    $envelope->setAmount(count($blanksValid));
                    $envelope->setLeadingZeros($firstBlank->getLeadingZeros());
                    $this->em->persist($envelope);
                    $this->em->flush();

                    $cnt = 0;
                    foreach ($blanksValid as &$blank) {
                        $oldStatus = $blank->getStatus();
                        $blank->setReferenceman($newReferenceman);
                        $blank->setReferencemanReferencemanEnvelope($envelope);
                        $blank->setStatus('appointedToReferencemanFromReferenceman');
                        $blank->setReferencemanApplied(null);
                        $blank->setOldReferenceman($oldReferenceman);

                        $this->em->persist($blank);

                        $oldReferenceman->removeReferencemanInterval(
                            $blank->getLegalEntity(),
                            $blank->getReferenceType(),
                            $blank->getSerie(),
                            $blank->getNumber(),
                            1,
                            $blank->getLeadingZeros()
                        );

                        $envelope->addInterval(
                            $blank->getLegalEntity(),
                            $blank->getReferenceType(),
                            $blank->getSerie(),
                            $blank->getNumber(),
                            1,
                            $blank->getLeadingZeros()
                        );

                        $this->em->persist($envelope);

                        $lifeLog = new BlankLifeLog();
                        $lifeLog->setBlank($blank);
                        $lifeLog->setOperationStatus($lifeLog::RR_REVERT_BLANK_TO_REFERENCE);
                        $lifeLog->setEnvelopeId($envelope->getId());
                        $lifeLog->setEnvelopeType('blank_referenceman_referenceman_envelope');

                        $lifeLog->setStartStatus($oldStatus);
                        $lifeLog->setEndStatus($blank->getStatus());

                        $lifeLog->setStartUser($this->getUser());
                        $lifeLog->setEndUser($newReferenceman);

                        $this->em->persist($oldReferenceman);
                        $this->em->persist($lifeLog);

                        if ($cnt % 100 == 0) {
                            $this->em->flush();
                            $this->em->detach($blank);
                            $this->em->detach($lifeLog);
                        }

                        ++$cnt;
                    }

                    $this->em->flush();
                    $this->em->clear();
                } elseif ($stockmanOrReferenceman->hasOneOfRoles('ROLE_STOCKMAN')) {
                    $stockman = &$stockmanOrReferenceman;
                    /** @var $stockman \KreaLab\CommonBundle\Entity\User */
                    $referenceman = $this->getUser();
                    /** @var $referenceman \KreaLab\CommonBundle\Entity\User */

                    $envelope = new BlankStockmanEnvelope();
                    $envelope->setReferenceman($referenceman);
                    $envelope->setStockman($stockman);
                    $envelope->setFirstNum($firstBlank->getNumber());
                    $envelope->setSerie($serie);
                    $envelope->setReferenceType($referenceType);
                    $envelope->setLegalEntity($legalEntity);
                    $envelope->setAmount(count($blanksValid));
                    $envelope->setLeadingZeros($firstBlank->getLeadingZeros());
                    $this->em->persist($envelope);
                    $this->em->flush();

                    $cnt = 0;
                    foreach ($blanksValid as &$blank) {
                        $blank->setReferenceman(null);
                        $blank->setOldReferenceman($referenceman);
                        $blank->setStockmanEnvelope($envelope);
                        $oldStatus = $blank->getStatus();
                        $blank->setStatus('appointedToStockman');
                        $blank->setReferencemanApplied(null);
                        $this->em->persist($blank);

                        $referenceman->removeReferencemanInterval(
                            $blank->getLegalEntity(),
                            $blank->getReferenceType(),
                            $blank->getSerie(),
                            $blank->getNumber(),
                            1,
                            $blank->getLeadingZeros()
                        );

                        $envelope->addInterval(
                            $blank->getLegalEntity(),
                            $blank->getReferenceType(),
                            $blank->getSerie(),
                            $blank->getNumber(),
                            1,
                            $blank->getLeadingZeros()
                        );

                        $lifeLog = new BlankLifeLog();
                        $lifeLog->setBlank($blank);
                        $lifeLog->setOperationStatus($lifeLog::RS_REVERT_BLANK_TO_STOCK);
                        $lifeLog->setEnvelopeId($envelope->getId());
                        $lifeLog->setEnvelopeType('blank_stockman_envelope');

                        $lifeLog->setStartStatus($oldStatus);
                        $lifeLog->setEndStatus($blank->getStatus());

                        $lifeLog->setStartUser($this->getUser());
                        $lifeLog->setEndUser($stockman);
                        $this->em->persist($lifeLog);

                        $this->em->persist($stockman);
                        $this->em->persist($referenceman);

                        if ($cnt % 100 == 0) {
                            $this->em->flush();
                            $this->em->detach($blank);
                            $this->em->detach($lifeLog);
                        }

                        ++$cnt;
                    }

                    $this->em->flush();
                    $this->em->clear();
                }
            }

            $this->em->commit();

            if ($lEntityId) {
                $this->addFlash('success', 'Передали');
                return $this->redirectToRoute('referenceman_blanks_not_in_operator_envelopes');
            } else {
                $this->addFlash('success', 'Передали');
                return $this->redirectToRoute('referenceman_blanks_transfer_blanks_to_stockman_or_referenceman');
            }
        }

        $referenceTypes = $this->em->getRepository('CommonBundle:ReferenceType')->createQueryBuilder('rt')
            ->addSelect('rt')->getQuery()->getArrayResult();

        $referenceTypesIdKeys = [];
        foreach ($referenceTypes as $referenceType) {
            $referenceTypesIdKeys[$referenceType['id']] = $referenceType;
        }

        $referenceTypeSeries = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('(b.serie) as serie, (b.reference_type) as reference_type')
            ->groupBy('serie')
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
            ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->getQuery()->getResult();

        $referenceTypeSeriesOutput = [];

        foreach ($referenceTypeSeries as $referenceTypeSerie) {
            $referenceTypeSeriesOutput[$referenceTypeSerie['reference_type']][]
                = (string)$referenceTypeSerie['serie'];
        }

        $info = [];

        if ($lEntityId and $refTypeId) {
            $legalEntity   = $this->em->getRepository('CommonBundle:LegalEntity')->find($lEntityId);
            $referenceType = $this->em->getRepository('CommonBundle:ReferenceType')->find($refTypeId);

            $info['legalEntityShortName'] = $legalEntity->getShortName();
            $info['referenceType']        = $referenceType->getName();
            $info['serie']                = $serieIn;
            $info['amount']               = $amount;
            $info['interval']             = '';

            $intervalsArr          = [];
            $referenceman          = $this->getUser(); /** @var $referenceman \KreaLab\CommonBundle\Entity\User */
            $referencemanIntervals = $referenceman->getReferencemanIntervals();
            $intervals             = $referencemanIntervals[$legalEntity->getId()][$referenceType->getId()][$serieIn];

            foreach ($intervals as $leadingZero => $curIntervals) {
                foreach ($curIntervals as $int) {
                    $int[0] = str_pad($int[0], $leadingZero, '0', STR_PAD_LEFT);
                    $int[1] = str_pad($int[1], $leadingZero, '0', STR_PAD_LEFT);

                    $intervalsArr['interval'] = ($int[0] == $int[1]) ? '['.$int[0].']' : '['.$int[0].', '.$int[1].']';
                    $intervalsArr['amount']   = $int[1] - $int[0] + 1;
                    $info['intervals'][]      = $intervalsArr;
                }
            }
        }

        $refTypeSelectedId = null;
        if (isset($data['reference_type'])) {
            $refTypeSelected   = $data['reference_type'];
            $refTypeSelectedId = $refTypeSelected->getId();
        }

        $lEntitySelectedId = null;
        if (isset($data['legal_entity'])) {
            $lEntitySelected   = $data['legal_entity'];
            $lEntitySelectedId = $lEntitySelected->getId();
        }

        $serieSelected = null;
        if (isset($data['serie'])) {
            $serieSelected = $data['serie'];
        }

        return $this->render('AppBundle:Referenceman:transfer_blanks_to_stockman_or_referenceman.html.twig', [
            'form'                  => $form->createView(),
            'form2'                 => $form2->createView(),
            'blanks'                => $blanks,
            'reference_types'       => $referenceTypesIdKeys,
            'reference_type_series' => $referenceTypeSeriesOutput,
            'refTypeSelectedId'     => $refTypeSelectedId,
            'lEntitySelectedId'     => $lEntitySelectedId,
            'serieSelected'         => $form->get('serie')->getData() ? $form->get('serie')->getData() : $serieSelected,
            'info'                  => $info,
        ]);
    }

    /**
     * @Route("/transfer-blanks-to-stockman-or-referenceman-{blankId}/",
     * name="referenceman_blanks_transfer_blanks_to_stockman_or_referenceman__blank_id")
     */
    public function referencemanBlanksTransferBlanksToStockmanOrReferencemanBlankId(Request $request, $blankId)
    {
        $blank = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.id = :id')->setParameter('id', $blankId)
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
            ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->getQuery()->getOneOrNullResult();
         /** @var $blank \KreaLab\CommonBundle\Entity\Blank */

        if (!$blank) {
            throw $this->createNotFoundException('Blank not found');
        }

        $fb = $this->get('form.factory')->createNamedBuilder('', FormType::class, [
            'amount'         => 1,
            'legal_entity'   => $blank->getLegalEntity(),
            'reference_type' => $blank->getReferenceType(),
            'first_num'      => $blank->getNumber(),
            'serie'          => $blank->getSerie(),
        ], [
            'translation_domain' => false,
            'csrf_protection'    => false,
        ]);
        $fb->add('legal_entity', EntityType::class, [
            'label'       => 'Юридическое лицо',
            'class'       => 'CommonBundle:LegalEntity',
            'disabled'    => true,
            'constraints' => new Assert\NotBlank(),

        ]);
        $fb->add('stockman_or_referenceman', EntityType::class, [
            'label'         => 'Кладовщик или справковед',
            'constraints'   => new Assert\NotBlank(),
            'class'         => 'CommonBundle:User',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.id != :userId')->setParameter('userId', $this->getUser())
                    ->andWhere('u.active = :active')->setParameter('active', true)
                    ->andWhere('(u.roles LIKE :role_stockman OR u.roles LIKE :role_referenceman)')
                    ->setParameter('role_stockman', '%ROLE_STOCKMAN%')
                    ->setParameter('role_referenceman', '%ROLE_REFERENCEMAN%')
                    ->addOrderBy('u.last_name')
                    ->addOrderBy('u.first_name')
                    ->addOrderBy('u.patronymic')
                    ;
            },
        ]);
        $fb->add('reference_type', EntityType::class, [
            'label'       => 'Тип справки',
            'class'       => 'CommonBundle:ReferenceType',
            'constraints' => new Assert\NotBlank(),
            'disabled'    => true,

        ]);

        if ($blank->getSerie()) {
            $fb->add('serie', ChoiceType::class, [
                'label'             => 'Серия',
                'choices'           => [$blank->getSerie() => $blank->getSerie()],
                'choices_as_values' => true,
                'disabled'          => true,
            ]);
        }

        $fb->add('first_num', IntegerType::class, [
            'label'       => 'Номер первого бланка',
            'disabled'    => true,
            'attr'        => ['min' => $blank->getNumber(), 'max' => $blank->getNumber()],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\EqualTo($blank->getNumber()),
            ],
        ]);
        $fb->add('amount', IntegerType::class, [
            'label'       => 'Количество',
            'attr'        => ['min' => 1, 'max' => 1],
            'disabled'    => true,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\EqualTo(1),
            ],
        ]);

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->em->beginTransaction();
            $stockmanOrReferenceman = $this->em->getRepository('CommonBundle:User')
                ->find($form->get('stockman_or_referenceman')->getData());

            if ($stockmanOrReferenceman->hasOneOfRoles('ROLE_REFERENCEMAN')) {
                /** @var $newReferenceman \KreaLab\CommonBundle\Entity\User */
                $newReferenceman = &$stockmanOrReferenceman;
                $oldReferenceman = $this->getUser(); /** @var $oldReferenceman \KreaLab\CommonBundle\Entity\User */

                $envelope = new BlankReferencemanReferencemanEnvelope();
                $envelope->setReferenceman($newReferenceman);
                $envelope->setOldReferenceman($oldReferenceman);
                $envelope->setFirstNum($form->get('first_num')->getData());

                if ($blank->getSerie()) {
                    $envelope->setSerie($form->get('serie')->getData());
                }

                $envelope->setReferenceType($form->get('reference_type')->getData());
                $envelope->setLegalEntity($form->get('legal_entity')->getData());
                $envelope->setAmount(1);
                $this->em->persist($envelope);
                $this->em->flush();

                $oldStatus = $blank->getStatus();
                $blank->setReferenceman($newReferenceman);
                $blank->setReferencemanReferencemanEnvelope($envelope);
                $blank->setStatus('appointedToReferencemanFromReferenceman');
                $blank->setReferencemanApplied(null);
                $blank->setOldReferenceman($oldReferenceman);

                $this->em->persist($blank);

                $oldReferenceman->removeReferencemanInterval(
                    $blank->getLegalEntity(),
                    $blank->getReferenceType(),
                    $blank->getSerie(),
                    $blank->getNumber(),
                    1,
                    $blank->getLeadingZeros()
                );

                $envelope->addInterval(
                    $blank->getLegalEntity(),
                    $blank->getReferenceType(),
                    $blank->getSerie(),
                    $blank->getNumber(),
                    1,
                    $blank->getLeadingZeros()
                );

                $this->em->persist($envelope);

                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::RR_REVERT_BLANK_TO_REFERENCE);
                $lifeLog->setEnvelopeId($envelope->getId());
                $lifeLog->setEnvelopeType('blank_referenceman_referenceman_envelope');

                $lifeLog->setStartStatus($oldStatus);
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($this->getUser());
                $lifeLog->setEndUser($newReferenceman);

                $this->em->persist($oldReferenceman);
                $this->em->persist($lifeLog);

                $this->em->flush();
                $this->em->clear();
            } elseif ($stockmanOrReferenceman->hasOneOfRoles('ROLE_STOCKMAN')) {
                $stockman     = &$stockmanOrReferenceman; /** @var $stockman \KreaLab\CommonBundle\Entity\User */
                $referenceman = $this->getUser(); /** @var $referenceman \KreaLab\CommonBundle\Entity\User */

                $envelope = new BlankStockmanEnvelope();
                $envelope->setReferenceman($referenceman);
                $envelope->setStockman($stockman);
                $envelope->setFirstNum($form->get('first_num')->getData());

                if ($blank->getSerie()) {
                    $envelope->setSerie($form->get('serie')->getData());
                }

                $envelope->setReferenceType($form->get('reference_type')->getData());
                $envelope->setLegalEntity($form->get('legal_entity')->getData());
                $envelope->setAmount(count(1));
                $this->em->persist($envelope);
                $this->em->flush();

                $blank->setReferenceman(null);
                $blank->setOldReferenceman($referenceman);
                $blank->setStockmanEnvelope($envelope);
                $oldStatus = $blank->getStatus();
                $blank->setStatus('appointedToStockman');
                $blank->setReferencemanApplied(null);
                $this->em->persist($blank);

                $referenceman->removeReferencemanInterval(
                    $blank->getLegalEntity(),
                    $blank->getReferenceType(),
                    $blank->getSerie(),
                    $blank->getNumber(),
                    1,
                    $blank->getLeadingZeros()
                );

                $envelope->addInterval(
                    $blank->getLegalEntity(),
                    $blank->getReferenceType(),
                    $blank->getSerie(),
                    $blank->getNumber(),
                    1,
                    $blank->getLeadingZeros()
                );

                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::RS_REVERT_BLANK_TO_STOCK);
                $lifeLog->setEnvelopeId($envelope->getId());
                $lifeLog->setEnvelopeType('blank_stockman_envelope');

                $lifeLog->setStartStatus($oldStatus);
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($this->getUser());
                $lifeLog->setEndUser($stockman);
                $this->em->persist($lifeLog);

                $this->em->persist($stockman);
                $this->em->persist($referenceman);

                $this->em->flush();
                $this->em->clear();
            }

            $this->em->commit();


            $this->addFlash('success', 'Передали');

            if ($blank->getSerie()) {
                return $this->redirectToRoute('referenceman_blanks_not_in_operator_envelopes__view_list', [
                    'lEntityId' => $blank->getLegalEntity()->getId(),
                    'refTypeId' => $blank->getReferenceType()->getId(),
                    'serie'     => $blank->getSerie(),
                ]);
            } else {
                return $this->redirectToRoute('referenceman_blanks_not_in_operator_envelopes__view_list', [
                    'lEntityId' => $blank->getLegalEntity()->getId(),
                    'refTypeId' => $blank->getReferenceType()->getId(),
                    'serie'     => '-',
                ]);
            }
        }

        return $this->render('AppBundle:Referenceman:transfer_blanks_to_stockman_or_referenceman_blank_id.html.twig', [
            'form'                  => $form->createView(),
        ]);
    }

    /** @Route("/operator-referenceman-envelopes/",
     *      name="referenceman_blanks_operator_referenceman_envelopes") */
    public function referencemanBlanksOperatorEnvelopesAction(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('last_name', TextType::class, [
            'label'    => 'Фамилия',
            'required' => false,
        ]);
        $fb->add('first_name', TextType::class, [
            'label'    => 'Имя',
            'required' => false,
        ]);
        $fb->add('patronymic', TextType::class, [
            'label'    => 'Отчество',
            'required' => false,
        ]);
        $fb->add('legal_entity', EntityType::class, [
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'required'      => false,
            'choice_label'  => 'nameAndShortName',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->innerJoin('le.blanks', 'b', 'WITH', 'b.status = :status')
                    ->setParameter('status', 'appointedToReferencemanFromOperator')
                    ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                ;
            },
        ]);
        $fb->add('reference_type', EntityType::class, [
            'label'         => 'Тип бланка',
            'class'         => 'CommonBundle:ReferenceType',
            'placeholder'   => ' - Выберите тип справки - ',
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('rt')
                    ->innerJoin('rt.blanks', 'b', 'WITH', 'b.status = :status')
                    ->setParameter('status', 'appointedToReferencemanFromOperator')
                    ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                    ;
            },
        ]);

        $qb = $this->em->getRepository('CommonBundle:BlankOperatorReferencemanEnvelope')->createQueryBuilder('bore')
            ->leftJoin('bore.operator', 'o')->addSelect('o')
            ->leftJoin('bore.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('bore.blanks', 'b', 'WITH', 'b.status = :status')
            ->setParameter('status', 'appointedToReferencemanFromOperator')
            ->addSelect('b')
            ->andWhere('bore.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('bore.referenceman_applied IS NULL')
        ;

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->get('last_name')->getData();
            if ($data) {
                $qb->andWhere('o.last_name LIKE :last_name')->setParameter('last_name', '%'.$data.'%');
            }

            $data = $form->get('first_name')->getData();
            if ($data) {
                $qb->andWhere('o.first_name LIKE :first_name')->setParameter('first_name', '%'.$data.'%');
            }

            $data = $form->get('patronymic')->getData();
            if ($data) {
                $qb->andWhere('o.patronymic LIKE :patronymic')->setParameter('patronymic', '%'.$data.'%');
            }

            $data = $form->get('legal_entity')->getData();
            if ($data) {
                $qb->andWhere('bore.legal_entity = :legal_entity')->setParameter('legal_entity', $data);
            }

            $data = $form->get('reference_type')->getData();
            if ($data) {
                $qb->andWhere('bore.reference_type = :reference_type')->setParameter('reference_type', $data);
            }
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Referenceman:envelopes_from_operator.html.twig', [
            'pagerfanta'  => $pagerfanta,
            'filter_form' => $form->createView(),
            'list_fields' => [
                'operator',
                ['legalEntityShortName', 'min_col text-left'],
                ['referenceType', 'min_col text-left'],
                ['serie', 'min_col text-left'],
                ['first_num', 'min_col text-right'],
                ['amount', 'min_col text-right'],
            ],
        ]);
    }

    /** @Route("/operator-referenceman-envelopes/get-{id}/",
     *     name="referenceman_blanks_get_operator_referenceman_envelope") */
    public function referencemanBlanksGetOperatorReferencemanEnvelopeAction($id)
    {
        $envelope = $this->em->getRepository('CommonBundle:BlankOperatorReferencemanEnvelope')->find($id);
        $envelope->setReferencemanApplied(new \DateTime());
        $this->em->persist($envelope);
        $this->em->flush();

        $blanks = $this->em->getRepository('CommonBundle:Blank')->findBy([
            'operator_referenceman_envelope' => $envelope,
            'status'                         => 'appointedToReferencemanFromOperator',
        ]);

        $referenceman = $this->getUser(); /** @var $referenceman \KreaLab\CommonBundle\Entity\User */

        foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $oldStatus = $blank->getStatus();
            $operator  = $blank->getOperator();
            $blank->setStatus('acceptedByReferenceman');
            $blank->setReferencemanApplied(new \DateTime());
            $blank->setOperatorEnvelope(null);
            $blank->setOperatorReferencemanEnvelope(null);
            $blank->setOperatorApplied(null);

            $this->em->persist($blank);

            $referenceman->addReferencemanInterval(
                $blank->getLegalEntity(),
                $blank->getReferenceType(),
                $blank->getSerie(),
                $blank->getNumber(),
                1,
                $blank->getLeadingZeros()
            );
            $this->em->persist($referenceman);

            $lifeLog = new BlankLifeLog();
            $lifeLog->setBlank($blank);
            $lifeLog->setOperationStatus($lifeLog::OR_ACCEPT_ENVELOP_ALL_BLANK_FROM_OPERATOR);
            $lifeLog->setEnvelopeId($envelope->getId());
            $lifeLog->setEnvelopeType('blank_operator_referenceman_envelope');

            $lifeLog->setStartStatus($oldStatus);
            $lifeLog->setEndStatus($blank->getStatus());

            $lifeLog->setStartUser($operator);
            $lifeLog->setEndUser($this->getUser());
            $this->em->persist($lifeLog);
        }

        $this->em->remove($envelope);
        $this->em->flush();

        $this->addFlash('success', 'Приняли.');
        return $this->redirectToRoute('referenceman_blanks_operator_referenceman_envelopes');
    }

    /** @Route("/operator-referenceman-envelopes/view-{id}/",
     *     name="referenceman_blanks_view_operator_referenceman_envelope") */
    public function referencemanBlanksViewOperatorReferencemanEnvelopeAction(Request $request, $id)
    {
        $blanks = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.operator_referenceman_envelope = :operator_referenceman_envelopeId')
            ->setParameter('operator_referenceman_envelopeId', $id)
            ->andWhere('b.status = :status')->setParameter('status', 'appointedToReferencemanFromOperator')
            ->getQuery()->execute()
        ;

        $blankChoices = [];
        foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $blankChoices[$blank->getId()] = $blank->getId();
        }

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('blanks', ChoiceType::class, [
            'choices_as_values' => true,
            'multiple'          => true,
            'expanded'          => true,
            'choices'           => $blankChoices,
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $blankIds     = $form->get('blanks')->getData();
            $referenceman = $this->getUser(); /** @var $referenceman \KreaLab\CommonBundle\Entity\User */
            $envelope     = null;
            foreach ($blankIds as &$blankId) {
                $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy([
                    'id'                             => $blankId,
                    'operator_referenceman_envelope' => $id,
                    'status'                         => 'appointedToReferencemanFromOperator',
                ]);
                if ($blank) {
                    $oldStatus  = $blank->getStatus();
                    $operator   = $blank->getOperator();
                    $opEnvelope = $blank->getOperatorEnvelope();
                    $blank->setStatus('acceptedByReferenceman');
                    $blank->setReferencemanApplied(new \DateTime());
                    $blank->setOperatorEnvelope(null);
                    $envelope = $blank->getOperatorReferencemanEnvelope();
                    $blank->setOperatorReferencemanEnvelope(null);
                    $blank->setOperatorApplied(null);

                    $referenceman->addReferencemanInterval(
                        $blank->getLegalEntity(),
                        $blank->getReferenceType(),
                        $blank->getSerie(),
                        $blank->getNumber(),
                        1,
                        $blank->getLeadingZeros()
                    );
                    $this->em->persist($blank);
                    $this->em->persist($referenceman);

                    $lifeLog = new BlankLifeLog();
                    $lifeLog->setBlank($blank);
                    $lifeLog->setOperationStatus($lifeLog::OR_ACCEPT_BLANK_FROM_OPERATOR);
                    $lifeLog->setEnvelopeId($opEnvelope->getId());
                    $lifeLog->setEnvelopeType('blank_operator_envelope');

                    $lifeLog->setStartStatus($oldStatus);
                    $lifeLog->setEndStatus($blank->getStatus());

                    $lifeLog->setStartUser($operator);
                    $lifeLog->setEndUser($this->getUser());
                    $this->em->persist($lifeLog);

                    $this->em->flush();
                }
            }

            $blanksAmount = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->select('COUNT(b.id) as b_cnt')
                ->leftJoin('b.operator_referenceman_envelope', 'ore')
                ->andWhere('b.status = :status')
                ->setParameter('status', 'appointedToReferencemanFromOperator')
                ->andWhere('b.operator_referenceman_envelope = :operator_referenceman_envelope')
                ->setParameter('operator_referenceman_envelope', $envelope)
                ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                ->andWhere('b.referenceman_applied IS NULL')
                ->getQuery()->getSingleScalarResult();

            if ($blanksAmount == 0 and !empty($envelope)) {
                    $this->em->remove($envelope);
                    $this->em->flush();
            }

            $this->addFlash('success', 'Приняли.');

            return $this->redirectToRoute('referenceman_blanks_operator_referenceman_envelopes');
        }

        return $this->render('AppBundle:Referenceman:view_envelope_from_operator.html.twig', [
            'form'   => $form->createView(),
            'blanks' => $blanks,
        ]);
    }

    /** @Route("/referenceman-referenceman-envelopes/",
     *      name="referenceman_blanks_referenceman_referenceman_envelopes") */
    public function referencemanBlanksReferencemanReferencemanEnvelopesAction(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('last_name', TextType::class, [
            'label'    => 'Фамилия',
            'required' => false,
        ]);
        $fb->add('first_name', TextType::class, [
            'label'    => 'Имя',
            'required' => false,
        ]);
        $fb->add('patronymic', TextType::class, [
            'label'    => 'Отчество',
            'required' => false,
        ]);

        $fb->add('legal_entity', EntityType::class, [
            'label'        => 'Юридическое лицо',
            'class'        => 'CommonBundle:LegalEntity',
            'placeholder'  => ' - Выберите юридическое лицо - ',
            'required'     => false,
            'choice_label' => 'nameAndShortName',
        ]);

        $fb->add('reference_type', EntityType::class, [
            'label'       => 'Тип бланка',
            'class'       => 'CommonBundle:ReferenceType',
            'placeholder' => ' - Выберите тип справки - ',
            'required'    => false,
        ]);

        $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT(b.serie) AS serie')
            ->andWhere('b.serie != :serie')->setParameter('serie', '')
            ->andWhere('b.status = :status')->setParameter('status', 'appointedToReferencemanFromReferenceman')
            ->orderBy('serie')
            ->getQuery()->execute();

        $seriesChoices['- Выберите серию -'] = '';
        foreach ($series as $serie) {
            $seriesChoices[$serie['serie']] = $serie['serie'];
        }

        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'choices'           => $seriesChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);

        $form = $fb->getForm();

        $qb = $this->em->getRepository('CommonBundle:BlankReferencemanReferencemanEnvelope')->createQueryBuilder('brre')
            ->leftJoin('brre.old_referenceman', 'oldr')->addSelect('oldr')
            ->leftJoin('brre.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('brre.blanks', 'b', 'WITH', 'b.status = :status')
            ->setParameter('status', 'appointedToReferencemanFromReferenceman')
            ->addSelect('b')
            ->andWhere('brre.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('brre.referenceman_applied IS NULL')
        ;

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->get('last_name')->getData();
            if ($data) {
                $qb->andWhere('oldr.last_name LIKE :last_name')->setParameter('last_name', '%'.$data.'%');
            }

            $data = $form->get('first_name')->getData();
            if ($data) {
                $qb->andWhere('oldr.first_name LIKE :first_name')->setParameter('first_name', '%'.$data.'%');
            }

            $data = $form->get('patronymic')->getData();
            if ($data) {
                $qb->andWhere('oldr.patronymic LIKE :patronymic')->setParameter('patronymic', '%'.$data.'%');
            }

            $data = $form->get('legal_entity')->getData();
            if ($data) {
                $qb->andWhere('brre.legal_entity = :legal_entity')->setParameter('legal_entity', $data);
            }

            $data = $form->get('reference_type')->getData();
            if ($data) {
                $qb->andWhere('brre.reference_type = :reference_type')->setParameter('reference_type', $data);
            }

            $data = $form->get('serie')->getData();
            if ($data) {
                $qb->andWhere('brre.serie = :serie')->setParameter('serie', $data);
            }
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Referenceman:envelopes_from_referenceman.html.twig', [
            'pagerfanta'  => $pagerfanta,
            'filter_form' => $form->createView(),
            'list_fields' => [
                'old_referenceman',
                ['legalEntityShortName', 'min_col text-left'],
                ['referenceType', 'min_col text-left'],
                ['serie', 'min_col text-left'],
                ['first_num', 'min_col text-right'],
                ['amount', 'min_col text-right'],
            ],
        ]);
    }

    /** @Route("/referenceman-referenceman-envelopes/get-{id}/",
     *     name="referenceman_blanks_get_referenceman_envelope") */
    public function referencemanBlanksGetReferencemanReferencemanEnvelopeAction($id)
    {
        $envelope = $this->em->getRepository('CommonBundle:BlankReferencemanReferencemanEnvelope')->find($id);
        $envelope->setReferencemanApplied(new \DateTime());
        $this->em->persist($envelope);
        $this->em->flush();

        $blanks = $this->em->getRepository('CommonBundle:Blank')->findBy([
            'referenceman_referenceman_envelope' => $envelope,
            'status'                             => 'appointedToReferencemanFromReferenceman',
        ]);

        /** @var $referenceman \KreaLab\CommonBundle\Entity\User */
        $referenceman = $this->getUser();

        foreach ($blanks as $blank) {  /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $oldStatus       = $blank->getStatus();
            $oldReferenceman = $blank->getOldReferenceman();
            $blank->setStatus('acceptedByReferenceman');
            $blank->setReferencemanApplied(new \DateTime());
            $blank->setReferencemanReferencemanEnvelope(null);
            $this->em->persist($blank);

            $referenceman->addReferencemanInterval(
                $blank->getLegalEntity(),
                $blank->getReferenceType(),
                $blank->getSerie(),
                $blank->getNumber(),
                1,
                $blank->getLeadingZeros()
            );
            $this->em->persist($referenceman);

            $lifeLog = new BlankLifeLog();
            $lifeLog->setBlank($blank);
            $lifeLog->setOperationStatus($lifeLog::RR_ACCEPT_ENVELOP_ALL_BLANK_FROM_REFERENCE);
            $lifeLog->setEnvelopeId($envelope->getId());
            $lifeLog->setEnvelopeType('blank_referenceman_referenceman_envelope');

            $lifeLog->setStartStatus($oldStatus);
            $lifeLog->setEndStatus($blank->getStatus());

            $lifeLog->setStartUser($oldReferenceman);
            $lifeLog->setEndUser($this->getUser());
            $this->em->persist($lifeLog);
        }

        $this->em->flush();

        $this->addFlash('success', 'Приняли.');
        return $this->redirectToRoute('referenceman_blanks_referenceman_referenceman_envelopes');
    }

    /** @Route("/referenceman-referenceman-envelopes/view-intervals-{id}/",
     *     name="referenceman_blanks_view_intervals_referenceman_referenceman_envelope") */
    public function referencemanBlanksViewIntervalsReferencemanReferencemanEnvelope($id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope */
        $envelope = $this->em->getRepository('CommonBundle:BlankReferencemanReferencemanEnvelope')->findOneBy([
            'id'           => $id,
            'referenceman' => $this->getUser(),
        ]);

        if (!$envelope) {
            throw $this->createNotFoundException('Envelope is not found');
        }

        $intervals = $envelope->getIntervals();
        $intervals = $intervals[$envelope->getLegalEntity()->getId()][$envelope->getReferenceType()->getId()];
        $intervals = array_pop($intervals);

        return $this->render('AppBundle:Referenceman:view_intervals_envelope_from_referenceman.html.twig', [
            'intervals' => $intervals,
            'envelope'  => $envelope,
        ]);
    }

    /** @Route("/referenceman-referenceman-envelopes/view-{id}/",
     *     name="referenceman_blanks_view_referenceman_referenceman_envelope") */
    public function referencemanBlanksViewReferencemanReferencemanEnvelope($id)
    {
        $blanks = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.referenceman_referenceman_envelope = :referenceman_referenceman_envelopeId')
            ->setParameter('referenceman_referenceman_envelopeId', $id)
            ->andWhere('b.status = :status')
            ->setParameter('status', 'appointedToReferencemanFromReferenceman')
            ->getQuery()->execute()
        ;

        return $this->render('AppBundle:Referenceman:view_envelope_from_referenceman.html.twig', [
            'blanks' => $blanks,
        ]);
    }

    /** @Route("/lost/", name="referenceman_blanks_lost") */
    public function blanksLostAction()
    {
        $blanks = $this->em->getRepository('CommonBundle:Blank')->findBy([
            'status' => 'lost',
        ]);

        return $this->render('AppBundle:Referenceman:blanks_lost.html.twig', [
            'blanks' => $blanks,
        ]);
    }

    /** @Route("/lost/check-{id}/", name="referenceman_blanks_lost_check") */
    public function blanksLostCheckAction($id)
    {
        $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy([
            'id'     => $id,
            'status' => 'lost',
        ]);
        if (!$blank) {
            throw $this->createNotFoundException();
        }

        $oldStatus = $blank->getStatus();
        $blank->setStatus('lostChecked');
        $this->em->persist($blank);

        /** @var $env \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
        $env     = $blank->getOperatorEnvelope();
        $lifeLog = new BlankLifeLog();
        $lifeLog->setBlank($blank);
        $lifeLog->setOperationStatus($lifeLog::OR_CONFIRM_LOST_BLANK);
        $lifeLog->setEnvelopeId($env->getId());
        $lifeLog->setEnvelopeType('blank_operator_referenceman_envelope');

        $lifeLog->setStartStatus($oldStatus);
        $lifeLog->setEndStatus($blank->getStatus());

        $lifeLog->setStartUser($blank->getOperator());
        $lifeLog->setEndUser($this->getUser());

        $this->em->persist($lifeLog);

        $this->em->flush();

        $this->addFlash('success', 'Утеря бланка подтверждена.');
        return $this->redirectToRoute('referenceman_blanks_lost');
    }

    /** @Route("/lost/undo-{id}/", name="referenceman_blanks_lost_undo") */
    public function blanksLostUndoAction($id)
    {
        /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
        $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy([
            'id'     => $id,
            'status' => 'lost',
        ]);
        if (!$blank) {
            throw $this->createNotFoundException();
        }

        $oldStatus = $blank->getStatus();
        $blank->setStatus('acceptedByOperator');
        $this->em->persist($blank);

        $env     = $blank->getOperatorEnvelope();
        $lifeLog = new BlankLifeLog();
        $lifeLog->setBlank($blank);
        $lifeLog->setOperationStatus($lifeLog::OR_NOT_CONFIRM_LOST_BLANK);
        $lifeLog->setEnvelopeId($env->getId());
        $lifeLog->setEnvelopeType('blank_operator_referenceman_envelope');

        $lifeLog->setStartStatus($oldStatus);
        $lifeLog->setEndStatus($blank->getStatus());

        $lifeLog->setStartUser($blank->getOperator());
        $lifeLog->setEndUser($this->getUser());

        $this->em->persist($lifeLog);

        $this->em->flush();

        $this->addFlash('success', 'Утеря бланка не подтверждена.');
        return $this->redirectToRoute('referenceman_blanks_lost');
    }

    /** @Route("/operator-cancelled/select-box/",
     *      name="referenceman_blanks__operator_referenceman_box__select") */
    public function operatorReferencemanBoxSelectAction(Request $request)
    {
        $box = $this->em->getRepository('CommonBundle:ReferencemanArchiveBox')->findOneBy([
            'closed_at'    => null,
            'referenceman' => $this->getUser(),
        ]);

        if ($box) {
            $text = $this->em->getRepository('AdminSkeletonBundle:Setting')
                ->get('current_referenceman_archive_box_text');
            $text = str_replace('{{ number_box }}', $box->getId(), $text);
        } else {
            $text = $this->em->getRepository('AdminSkeletonBundle:Setting')
                ->get('creating_referenceman_archive_box_text');
        }

        if ($request->isMethod('post')) {
            $oldBox  = null;
            $session = $this->get('session');

            $blankIds = $session->get('cancelled_operator_blanks');
            if (!$blankIds) {
                throw $this->createNotFoundException('Нет бланков');
            }

            if (!$box) {
                $box = new ReferencemanArchiveBox();
                $box->setReferenceman($this->getUser());
                $box->setType('cancelled_blanks');
            }

            if ($request->get('action') == 'close') {
                $box->setClosedAt(new \DateTime());
                $this->em->persist($box);
                $this->em->flush();
                $oldBox = $box;

                $box = new ReferencemanArchiveBox();
                $box->setReferenceman($this->getUser());
                $box->setType('cancelled_blanks');
            }

            $blanks = [];
            foreach ($blankIds as $blankId) {
                $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy([
                    'id'           => $blankId,
                    'status'       => 'cancelledByOperator',
                    'referenceman' => $this->getUser(),
                ]);

                if (!$blank) {
                    throw $this->createNotFoundException('Нет бланка');
                }

                $blanks[] = $blank;
                $box->addBlank($blank);
                $box->setOperator($blank->getOperator());
                $box->setSerie($blank->getSerie());
                $box->setType('cancelled_blanks');

                $this->em->persist($box);
                $blank->setReferencemanArchiveBox($box);
                $this->em->persist($blank);
                $this->em->flush();

                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::OR_ARHIVED_BY_REFERENCE);

                $lifeLog->setStartStatus($blank->getStatus());
                $blank->setStatus('archivedByReferenceman');
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($this->getUser());
                $lifeLog->setEndUser($this->getUser());

                $this->em->persist($blank);
                $this->em->persist($lifeLog);
                $this->em->persist($box);
                $this->em->flush();
            }

            $action = $request->get('action');

            if ($action) {
                return $this->render('AppBundle:Referenceman:operator_referenceman_box__info.html.twig', [
                    'action' => $action,
                    'oldBox' => $oldBox,
                    'box'    => $box,
                    'blanks' => $blanks,
                ]);
            } else {
                return $this->redirectToRoute('referenceman_blanks_operator_cancelled');
            }
        };

        return $this->render('AppBundle:Referenceman:operator_referenceman_box__select.html.twig', [
            'box'  => $box,
            'text' => $text,
        ]);
    }

    /** @Route("/referenceman-cancelled/select-box/",
     *      name="referenceman_blanks__referenceman_referenceman_box__select") */
    public function referencemanReferencemanBoxSelectAction(Request $request)
    {
        $box = $this->em->getRepository('CommonBundle:ReferencemanArchiveBox')->findOneBy([
            'closed_at'    => null,
            'referenceman' => $this->getUser(),
        ]);

        if ($request->isMethod('post')) {
            $oldBox  = null;
            $session = $this->get('session');

            $blankIds = $session->get('cancelled_referenceman_blanks');
            if (!$blankIds) {
                throw $this->createNotFoundException('Нет бланков');
            }

            if (!$box) {
                $box = new ReferencemanArchiveBox();
                $box->setReferenceman($this->getUser());
                $box->setType('cancelled_blanks');
            }

            if ($request->get('action') == 'close') {
                $box->setClosedAt(new \DateTime());
                $this->em->persist($box);
                $this->em->flush();
                $oldBox = $box;

                $box = new ReferencemanArchiveBox();
                $box->setReferenceman($this->getUser());
                $box->setType('cancelled_blanks');
            }

            $blankWithNoStamp = false;

            $blanks = [];
            foreach ($blankIds as $blankId) {
                $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy([
                    'id'           => $blankId,
                    'status'       => 'brokenByReferenceman',
                    'referenceman' => $this->getUser(),
                ]);

                if (!$blank) {
                    $blankWithNoStamp = true;

                    $blank = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                        ->innerJoin('b.replaced_by_blank_with_stamp', 'rbwns')
                        ->andWhere('b.status = :status')->setParameter('status', 'replacedBecauseNoStampByOperator')
                        ->andWhere('b.id = :id')->setParameter('id', $blankId)
                        ->getQuery()->getOneOrNullResult();
                }

                if (!$blank) {
                    throw $this->createNotFoundException('Нет бланка');
                }

                $blanks[] = $blank;
                $box->addBlank($blank);
                $box->setSerie($blank->getSerie());
                $box->setType('cancelled_blanks');
                $box->setLegalEntity($blank->getLegalEntity());
                $box->setReferenceType($blank->getReferenceType());

                $this->em->persist($box);
                $blank->setReferencemanArchiveBox($box);
                $blank->setReferenceman($this->getUser());
                $this->em->persist($blank);
                $this->em->flush();

                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::OR_ARHIVED_BROKEN_BY_REFERENCE);

                $lifeLog->setStartStatus($blank->getStatus());
                $blank->setStatus('archivedByReferenceman');
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($this->getUser());
                $lifeLog->setEndUser($this->getUser());

                $this->em->persist($blank);
                $this->em->persist($lifeLog);

                $this->em->flush();
            }

            $action = $request->get('action');

            if ($action) {
                return $this->render('AppBundle:Referenceman:referenceman_referenceman_box__info.html.twig', [
                    'action' => $action,
                    'oldBox' => $oldBox,
                    'box'    => $box,
                    'blanks' => $blanks,
                ]);
            } elseif ($blankWithNoStamp) {
                return $this->redirectToRoute('referenceman_blanks__blanks_with_no_stamps');
            } else {
                return $this->redirectToRoute('referenceman_blanks__referenceman_referenceman_broken__list');
            }
        };

        if (empty($box)) {
            $text = $this->em->getRepository('AdminSkeletonBundle:Setting')->findOneBy([
                '_key' => 'creating_referenceman_archive_box_text',
            ]);
            $text = $text->getValue();
        } else {
            $text = $this->em->getRepository('AdminSkeletonBundle:Setting')->findOneBy([
                '_key' => 'current_referenceman_archive_box_text',
            ]);

            if ($text) {
                $text = $text->getValue();
                $text = str_replace('{{ number_box }}', $box->getId(), $text);
            }
        }

        return $this->render('AppBundle:Referenceman:referenceman_referenceman_box__select.html.twig', [
            'box'  => $box,
            'text' => $text,
        ]);
    }

    /** @Route("/referenceman-referenceman-broken-blanks/add-broken-blank/",
     *     name="referenceman_blanks__referenceman_referenceman_broken__add") */
    public function addReferencemanReferencemanBrokenBlankAction(Request $request)
    {
        $lEntityId = $request->get('lEntityId');
        $refTypeId = $request->get('refTypeId');
        $serieIn   = $request->get('serieIn');
        $number    = $request->get('number');

        $data = [];

        if (!empty($number)) {
            $data['number'] = $number;
        }

        if ($lEntityId) {
            $legalEntity = $this->em->getRepository('CommonBundle:LegalEntity')->find($lEntityId);
            if (!$legalEntity) {
                throw $this->createNotFoundException('Legal entity not found');
            }

            $data['legal_entity'] = $legalEntity;
        }

        if ($refTypeId) {
            $referenceType = $this->em->getRepository('CommonBundle:ReferenceType')->find($refTypeId);
            if (!$referenceType) {
                throw $this->createNotFoundException('Reference type not found');
            }

            $data['reference_type'] = $referenceType;
        }

        $serie         = null;
        $seriesChoices = [];
        if (!empty($serieIn) and $serieIn != '-') {
            $serie = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->select('DISTINCT(b.serie) AS serie')
                ->andWhere('b.serie = :serie')->setParameter('serie', $serieIn)
                ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
                ->getQuery()->getSingleScalarResult();
            if (!$serie) {
                throw $this->createNotFoundException('Serie not found');
            }

            $data['serie']         = $serie;
            $seriesChoices[$serie] = $serie;
        } else {
            $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->select('DISTINCT(b.serie) AS serie')
                ->andWhere('b.serie IS NOT NULL')
                ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
                ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                ->orderBy('serie')
                ->getQuery()->execute();
            foreach ($series as $serie) {
                $seriesChoices[$serie['serie']] = $serie['serie'];
            }
        }

        $referenceTypes = $this->em->getRepository('CommonBundle:ReferenceType')->createQueryBuilder('rt')
            ->select('rt.id, rt.name')
            ->getQuery()->execute();

        $referenceTypesChoices = [];
        foreach ($referenceTypes as $referenceType) {
            $referenceTypesChoices[$referenceType['name']] = $referenceType['id'];
        }

        $fb = $this->createFormBuilder(null, [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('legal_entity', EntityType::class, [
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'choice_label'  => 'nameAndShortName',
            'mapped'        => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->andWhere('le.active = :active')->setParameter('active', true)
                    ;
            },
        ]);
        $fb->add('reference_type', EntityType::class, [
            'label'       => 'Тип справки',
            'class'       => 'CommonBundle:ReferenceType',
            'placeholder' => ' - Выберите тип справки - ',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'placeholder'       => ' - Выберите серию - ',
            'choices'           => $seriesChoices,
            'choices_as_values' => true,
        ]);
        $fb->add('number', TextType::class, [
            'label'       => 'Номер бланка',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);

        $fb->setData($data);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy([
                'serie'          => $seriesChoices[$form->get('serie')->getData()],
                'number'         => (int)$form->get('number')->getData(),
                'reference_type' => $form->get('reference_type')->getData(),
                'legal_entity'   => $form->get('legal_entity')->getData(),
                'status'         => 'acceptedByReferenceman',
                'referenceman'   => $this->getUser(),
                'leading_zeros'  => strlen($form->get('number')->getData()),
            ]);
            if ($blank) {
                $oldStatus = $blank->getStatus();
                $blank->setStatus('brokenByReferenceman');
                $blank->setOperatorApplied(null);
                $blank->setReferencemanApplied(null);
                $this->em->persist($blank);

                /** @var $referenceman \KreaLab\CommonBundle\Entity\User */
                $referenceman = $this->getUser();
                $referenceman->removeReferencemanInterval(
                    $blank->getLegalEntity(),
                    $blank->getReferenceType(),
                    $blank->getSerie(),
                    $blank->getNumber(),
                    1,
                    $blank->getLeadingZeros()
                );

                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::R_BROKEN_BY_REFERENCEMAN);

                $lifeLog->setStartStatus($oldStatus);
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($this->getUser());
                $lifeLog->setEndUser($this->getUser());

                $this->em->persist($lifeLog);

                $this->em->flush();
                $this->addFlash('success', 'Принято');

                if (!empty($data)) {
                    return $this->redirectToRoute('referenceman_blanks_not_in_operator_envelopes__view_list', [
                        'lEntityId' => $lEntityId,
                        'refTypeId' => $refTypeId,
                        'serie'     => empty($serieIn) ? '-' : $serieIn,
                    ]);
                }

                return $this->redirectToRoute('referenceman_blanks__referenceman_referenceman_broken__add');
            } else {
                $this->addFlash('danger', 'Не найден');
            }
        }

        $blanks = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT b.serie, (b.reference_type) as reference_type, (b.legal_entity) as legal_entity')
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
            ->getQuery()->getResult();

        $blanksFilter = [];
        foreach ($blanks as $blank) {
            $blanksFilter[$blank['legal_entity']][$blank['reference_type']][] = (string)$blank['serie'];
        }

        $referenceTypes = $this->em->getRepository('CommonBundle:ReferenceType')->createQueryBuilder('rt')
            ->addSelect('rt')->getQuery()->getArrayResult();

        $referenceTypesIdKeys = [];
        foreach ($referenceTypes as $referenceType) {
            $referenceTypesIdKeys[$referenceType['id']] = $referenceType;
        }

        return $this->render('AppBundle:Referenceman:add_referenceman_referenceman_broken_blank.html.twig', [
            'form'                    => $form->createView(),
            'blanks'                  => $blanksFilter,
            'reference_types'         => $referenceTypesIdKeys,
            'legal_entity_selected'   => !empty($data['legal_entity']) ? $data['legal_entity']->getId() : '',
            'serie_selected'          => !empty($data['serie']) ? $data['serie'] : $form->get('serie')->getViewData(),
            'reference_type_selected' => !empty($data['reference_type']) ? $data['reference_type']->getId()
                : $form->get('reference_type')->getViewData(),
        ]);
    }

    /** @Route("/referenceman-referenceman-broken-blanks/",
     *  name="referenceman_blanks__referenceman_referenceman_broken__list") */
    public function referencemanReferencemanBrokenBlanksAction(Request $request)
    {
        $blanks = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.status = :status')->setParameter('status', 'brokenByReferenceman')
            ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->getQuery()->execute();

        $blankChoices = [];
        foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $blankChoices[$blank->getId()] = $blank->getId();
        }

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('blanks', ChoiceType::class, [
            'choices_as_values' => true,
            'multiple'          => true,
            'expanded'          => true,
            'choices'           => $blankChoices,
        ]);
        $fb->add('save', SubmitType::class, [
            'label' => 'Подтвердить',
            'attr'  => ['class' => 'btn btn-success pull-right'],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $session = $this->get('session');
            $session->set('cancelled_referenceman_blanks', $form->get('blanks')->getData());
            return $this->redirectToRoute('referenceman_blanks__referenceman_referenceman_box__select');
        }

        return $this->render('AppBundle:Referenceman:referenceman_referenceman_broken_blanks.html.twig', [
            'form'   => $form->createView(),
            'blanks' => $blanks,
        ]);
    }

    /** @Route("/arhival-boxes/",
     *  name="referenceman_blanks__arhival_boxes") */
    public function arhivalBoxesAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:ReferencemanArchiveBox')
            ->createQueryBuilder('box')
            ->andWhere('box.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->orderBy('box.id', 'DESC')
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Referenceman:arhival_boxes.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /** @Route("/arhival-boxes/view-desc-{id}/",
     *  name="referenceman_blanks__arhival_box__view_desc") */
    public function arhivalBoxViewDescAction($id)
    {
        $blank = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
            ->andWhere('b.status = :status')->setParameter('status', 'archivedByReferenceman')
            ->andWhere('b.id = :id')->setParameter('id', $id)
            ->andWhere('b.referenceman_archive_box is NOT NULL')
            ->getQuery()->getOneOrNullResult();

        if (!$blank) {
            throw $this->createNotFoundException();
        }

        return $this->render('AppBundle:Referenceman:arhival_box__view_desc.html.twig', [
            'blank' => $blank,
        ]);
    }

    /** @Route("/arhival-boxes/view-{id}/",
     *  name="referenceman_blanks__arhival_box") */
    public function arhivalBoxAction(Request $request, $id)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);

        $fb->add('user', TextType::class, [
            'label'    => 'Пользователь',
            'required' => false,
        ]);

        $fb->add('legal_entity', EntityType::class, [
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'choice_label'  => 'nameAndShortName',
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->innerJoin('le.blanks', 'b', 'WITH', 'b.status = :status')
                    ->setParameter('status', 'archivedByReferenceman')
                    ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                    ;
            },
        ]);

        $fb->add('reference_type', EntityType::class, [
            'label'         => 'Тип бланка',
            'class'         => 'CommonBundle:ReferenceType',
            'placeholder'   => ' - Выберите тип бланка - ',
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('rt')
                    ->innerJoin('rt.blanks', 'b', 'WITH', 'b.status = :status')
                    ->setParameter('status', 'archivedByReferenceman')
                    ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                ;
            },
        ]);

        $stampChoices['- Выберите статус печати -'] = '';
        $stampChoices['С печатью']                  = 'with_stamp';
        $stampChoices['Без печати']                 = 'with_no_stamp';

        $fb->add('stamp', ChoiceType::class, [
            'label'             => 'Печать',
            'choices'           => $stampChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);

        $qb = $this->em->getRepository('CommonBundle:Blank')
            ->createQueryBuilder('b')
            ->leftJoin('b.referenceman', 'r')
            ->leftJoin('b.operator', 'o')
            ->andWhere('b.referenceman_archive_box = :referenceman_archive_box')
            ->setParameter('referenceman_archive_box', $id)
            ->orderBy('b.updated_at', 'desc')
        ;

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->get('user')->getData();
            if ($data) {
                $qb->andWhere('r.last_name LIKE :r_last_name OR r.first_name LIKE :r_first_name 
                     OR r.patronymic LIKE :r_patronymic OR o.last_name LIKE :o_last_name 
                     OR o.first_name LIKE :o_first_name OR o.patronymic LIKE :o_patronymic')
                    ->setParameter('r_last_name', '%'.$data.'%')
                    ->setParameter('r_first_name', '%'.$data.'%')
                    ->setParameter('r_patronymic', '%'.$data.'%')
                    ->setParameter('o_last_name', '%'.$data.'%')
                    ->setParameter('o_first_name', '%'.$data.'%')
                    ->setParameter('o_patronymic', '%'.$data.'%');
            }

            $data = $form->get('legal_entity')->getData();
            if ($data) {
                $qb->andWhere('b.legal_entity = :legal_entity')->setParameter('legal_entity', $data);
            }

            $data = $form->get('reference_type')->getData();
            if ($data) {
                $qb->andWhere('b.reference_type = :reference_type')->setParameter('reference_type', $data);
            }

            $data = $form->get('stamp')->getData();
            if ($data == 'with_stamp') {
                $qb->andWhere('b.stamp = :stamp')->setParameter('stamp', true);
            }

            if ($data == 'with_no_stamp') {
                $qb->andWhere('b.stamp = :stamp')->setParameter('stamp', false);
            }
        }

        $blanks = $qb->getQuery()->getResult();

        return $this->render('AppBundle:Referenceman:arhival_box.html.twig', [
            'blanks'      => $blanks,
            'filter_form' => $form->createView(),
        ]);
    }

    /** @Route("/blanks-with-no-stamps/get-{id}/",
     *  name="referenceman_blanks__blanks_with_no_stamps__get") */
    public function blanksWithNoStampsGetAction($id)
    {
        $session = $this->get('session');
        $session->set('cancelled_referenceman_blanks', [$id]);

        return $this->redirectToRoute('referenceman_blanks__referenceman_referenceman_box__select');
    }

    /** @Route("/blanks-with-no-stamps/",
     *  name="referenceman_blanks__blanks_with_no_stamps") */
    public function blanksWithNoStampsAction(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('last_name', TextType::class, [
            'label'    => 'Фамилия',
            'required' => false,
        ]);
        $fb->add('first_name', TextType::class, [
            'label'    => 'Имя',
            'required' => false,
        ]);
        $fb->add('patronymic', TextType::class, [
            'label'    => 'Отчество',
            'required' => false,
        ]);

        $fb->add('legal_entity', EntityType::class, [
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'choice_label'  => 'nameAndShortName',
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->innerJoin('le.blanks', 'b', 'WITH', 'b.status = :status')
                    ->setParameter('status', 'acceptedByReferenceman')
                    ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                    ->andWhere('b.stamp = :stamp')->setParameter('stamp', false)
                    ;
            },
        ]);

        $fb->add('reference_type', EntityType::class, [
            'label'         => 'Тип бланка',
            'class'         => 'CommonBundle:ReferenceType',
            'placeholder'   => ' - Выберите тип бланка - ',
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('rt')
                    ->innerJoin('rt.blanks', 'b', 'WITH', 'b.status = :status')
                    ->setParameter('status', 'acceptedByReferenceman')
                    ->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $this->getUser())
                    ->andWhere('b.stamp = :stamp')->setParameter('stamp', false)
                    ;
            },
        ]);

        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->leftJoin('b.operator', 'o')
            ->innerJoin('b.replaced_by_blank_with_stamp', 'rbwns')
            ->andWhere('b.status = :status')->setParameter('status', 'replacedBecauseNoStampByOperator')
        ;

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->get('last_name')->getData();
            if ($data) {
                $qb->andWhere('o.last_name LIKE :last_name')->setParameter('last_name', '%'.$data.'%');
            }

            $data = $form->get('first_name')->getData();
            if ($data) {
                $qb->andWhere('o.first_name LIKE :first_name')->setParameter('first_name', '%'.$data.'%');
            }

            $data = $form->get('patronymic')->getData();
            if ($data) {
                $qb->andWhere('o.patronymic LIKE :patronymic')->setParameter('patronymic', '%'.$data.'%');
            }

            $data = $form->get('legal_entity')->getData();
            if ($data) {
                $qb->andWhere('b.legal_entity = :legal_entity')->setParameter('legal_entity', $data);
            }

            $data = $form->get('reference_type')->getData();
            if ($data) {
                $qb->andWhere('b.reference_type = :reference_type')->setParameter('reference_type', $data);
            }
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Referenceman:blanks_with_no_stamps.html.twig', [
            'pagerfanta'  => $pagerfanta,
            'filter_form' => $form->createView(),
        ]);
    }
}
