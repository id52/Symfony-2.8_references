<?php

namespace KreaLab\AppBundle\Controller;

use KreaLab\CommonBundle\Entity\Blank;
use KreaLab\CommonBundle\Entity\BlankLifeLog;
use KreaLab\CommonBundle\Entity\BlankLog;
use KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope;
use KreaLab\CommonBundle\Util\BlankIntervals;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormError;

/** @Route("/stockman-blanks") */
class StockmanController extends Controller
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
        $this->denyAccessUnlessGranted('ROLE_STOCKMAN');
    }

    /** @Route("/stockman-referenceman-envelopes/view-intervals-{id}/",
     *       name="stockman_blanks__stockman_referenceman_envelopes__view_intervals") */
    public function stockmanBlanksViewIntervalsStockmanReferencemanEnvelope($id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankReferencemanEnvelope */
        $envelope = $this->em->getRepository('CommonBundle:BlankReferencemanEnvelope')->findOneBy([
            'id'       => $id,
            'stockman' => $this->getUser(),
        ]);

        if (!$envelope) {
            throw $this->createNotFoundException('Envelope is not found');
        }

        $intervals = $envelope->getIntervals();
        $intervals = $intervals[$envelope->getLegalEntity()->getId()][$envelope->getReferenceType()->getId()];
        $intervals = array_pop($intervals);

        return $this->render('AppBundle:Stockman:view_envelope_for_referenceman_intervals.html.twig', [
            'intervals' => $intervals,
            'envelope'  => $envelope,
        ]);
    }

    /** @Route("/stockman-referenceman-envelopes/view-after-creating-{id}/",
     *      name="stockman_blanks__stockman_referenceman_envelope__view_after_creating") */
    public function stockmanBlanksViewStockmanReferencemanEnvelopeAfterCreating($id)
    {
        $envelope = $this->em->getRepository('CommonBundle:BlankReferencemanEnvelope')->createQueryBuilder('bre')
            ->leftJoin('bre.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('bre.blanks', 'b')->addSelect('b')
            ->andWhere('bre.stockman = :stockman')->setParameter('stockman', $this->getUser())
            ->andWhere('bre.referenceman_applied is null')
            ->andWhere('bre.id = :id')->setParameter('id', $id)
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

        $intervals = $envelope->getIntervals();

        return $this->render('AppBundle:Stockman:view_envelope_for_referenceman_after_creating.html.twig', [
            'envelope'        => $envelope,
            'filter_form'     => null,
            'reference_types' => $referenceTypes,
            'legal_entities'  => $legalEntities,
            'intervals'       => $intervals,
        ]);
    }

    /** @Route("/", name="stockman_blanks") */
    public function stockmanBlanksAction(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);

        $fb->add('start_time', DateType::class, [
            'label'    => 'Создано от',
            'required' => false,
        ]);
        $fb->add('end_time', DateType::class, [
            'label'    => 'Создано до',
            'required' => false,
        ]);

        $fb->add('legal_entity', EntityType::class, [
            'required'      => false,
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'choice_label'  => 'nameAndShortName',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->andWhere('le.active = :active')->setParameter('active', true)
                    ->innerJoin('le.blank_logs', 'bl')->addSelect('bl')
                    ;
            },
        ]);

        $fb->add('reference_type', EntityType::class, [
            'required'      => false,
            'label'         => 'Тип бланка',
            'class'         => 'CommonBundle:ReferenceType',
            'placeholder'   => ' - Выберите тип бланка - ',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('rt')
                    ->innerJoin('rt.blank_logs', 'bl')->addSelect('bl')
                    ;
            },
        ]);

        $qb = $this->em->getRepository('CommonBundle:BlankLog')->createQueryBuilder('bl')
            ->andWhere('bl.stockman = :stockman')->setParameter('stockman', $this->getUser())
            ->addOrderBy('bl.id', 'desc')
        ;

        $fb->setMethod('get');
        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->get('start_time')->getData();
            if ($data) {
                $qb->andWhere('bl.created_at >= :start_time')->setParameter('start_time', $data);
            }

            $data = $form->get('end_time')->getData();
            if ($data) {
                $qb->andWhere('bl.created_at <= :end_time')->setParameter('end_time', $data);
            }

            $data = $form->get('legal_entity')->getData();
            if ($data) {
                $qb->andWhere('bl.legal_entity = :legal_entity')->setParameter('legal_entity', $data);
            }

            $data = $form->get('reference_type')->getData();
            if ($data) {
                $qb->andWhere('bl.reference_type = :reference_type')->setParameter('reference_type', $data);
            }
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Stockman:blanks_log.html.twig', [
            'pagerfanta'  => $pagerfanta,
            'filter_form' => $form->createView(),
        ]);
    }

     /**  @Route("/add/", name="stockman_blanks_add") */
    public function stockmanBlanksAddAction(Request $request)
    {
        $lEntityId    = $request->get('lEntityId');
        $refTypeId    = $request->get('refTypeId');
        $serie        = $request->get('serie');
        $leadingZeros = $request->get('leadingZeros');

        $maxResult = 1000;

        $data = [];
        if ($lEntityId and $refTypeId) {
            $data['first_num']      = null;
            $data['amount']         = null;
            $data['legal_entity']   = $this->em->find('CommonBundle:LegalEntity', $lEntityId);
            $data['reference_type'] = $this->em->find('CommonBundle:ReferenceType', $refTypeId);
            $data['leading_zeros']  = $leadingZeros;
        }

        if ($serie) {
            $data['serie'] = $serie;
        }

        $fb = $this->createFormBuilder($data, ['translation_domain' => false]);
        $fb->add('legal_entity', EntityType::class, [
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'choice_label'  => 'nameAndShortName',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->andWhere('le.active = :active')->setParameter('active', true)
                ;
            },
        ]);
        $fb->add('leading_zeros', IntegerType::class, [
            'label'       => 'Разрядность',
            'required'    => true,
            'attr'        => ['min' => 0],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\GreaterThanOrEqual(0),
            ],
        ]);
        $fb->add('reference_type', EntityType::class, [
            'label'       => 'Тип справки',
            'class'       => 'CommonBundle:ReferenceType',
            'placeholder' => ' - Выберите тип справки - ',
        ]);
        $fb->add('serie', TextType::class, [
            'label'       => 'Серия',
            'required'    => false,
            'constraints' => [
                new Assert\Regex([
                    'pattern' => '/^[a-zA-Zа-яА-Я0-9]+[a-zA-Zа-яА-Я0-9-]*$/u',
                    'message' => 'Введите данные в формате: 0-9, a-z, A-Z, а-я, А-Я, -',
                ]),
            ],
        ]);
        $fb->add('first_num', TextType::class, [
            'label'       => 'Номер первого бланка',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
        $fb->add('amount', IntegerType::class, [
            'label'       => 'Количество',
            'required'    => true,
            'attr'        => ['min' => 1, 'max' => $maxResult],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Range(['min' => 1, 'max' => $maxResult]),
            ],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($request->isMethod('post')) {
            $leadingZeros = $form->get('leading_zeros')->getData();
            $firstNum     = $form->get('first_num')->getData();

            if (strlen($firstNum) != $leadingZeros) {
                $form->get('first_num')
                    ->addError(new FormError('Номер не совпадает с количеством указанных разрядов'));
            }

            /** @var $referenceType \KreaLab\CommonBundle\Entity\ReferenceType */
            $referenceType = $form->get('reference_type')->getData();
            $legalEntity   = $form->get('legal_entity')->getData();
            $serie         = '';
            if ($referenceType->getIsSerie() and empty($form->get('serie')->getData())) {
                $form->get('serie')->addError(new FormError('Значение не должно быть пустым'));
            }

            if ($referenceType->getIsSerie() and !empty($form->get('serie')->getData())) {
                $serie = $form->get('serie')->getData();
            }

            if (!$referenceType->getIsSerie()) {
                $serie = '';
            }

            if ($form->isValid()) {
                $amount    = (int)$form->get('amount')->getData();
                $finishNum = $firstNum + $amount - 1;
                $firstNum  = (int)$firstNum;

                $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                    ->select('count(b.id) as cnt')
                    ->andWhere('b.number >= :number')->setParameter('number', $firstNum)
                    ->andWhere('b.number <= :finish_number')->setParameter('finish_number', $finishNum)
                    ->andWhere('b.reference_type = :reference_type')
                    ->setParameter('reference_type', $form->get('reference_type')->getData())
                    ->andWhere('b.legal_entity = :legal_entity')
                    ->setParameter('legal_entity', $form->get('legal_entity')->getData())
                ;

                if ($referenceType->getIsSerie()) {
                    $qb->andWhere('b.serie = :serie')->setParameter('serie', $serie);
                }

                $blankAmount = $qb->getQuery()->getSingleScalarResult();

                if ($blankAmount == 0) {
                    $this->em->beginTransaction();
                    $blankLog = new BlankLog();
                    $blankLog->setAmount($form->get('amount')->getData());
                    $blankLog->setFirstNum($form->get('first_num')->getData());

                    if ($referenceType->getIsSerie()) {
                        $blankLog->setSerie($serie);
                    }

                    $blankLog->setReferenceType($form->get('reference_type')->getData());
                    $blankLog->setStockman($this->getUser());
                    $blankLog->setLegalEntity($form->get('legal_entity')->getData());
                    $this->em->persist($blankLog);
                    $this->em->flush();

                    /** @var $user \KreaLab\CommonBundle\Entity\User */
                    $user = $this->getUser();

                    $blank      = null;
                    $firstBlank = null;
                    $lastBlank  = null;

                    for ($numBlank = $firstNum; $numBlank <= $finishNum; $numBlank ++) {
                        $blank = new Blank();
                        $blank->setLegalEntity($form->get('legal_entity')->getData());
                        $blank->setReferenceType($form->get('reference_type')->getData());
                        $blank->setSerie($serie);
                        $blank->setStockman($user);
                        $blank->setNumber($numBlank);
                        $blank->setStatus('new');
                        $blank->setBlankLog($blankLog);
                        $blank->setLeadingZeros($leadingZeros);
                        $this->em->persist($blank);

                        $user->addInterval(
                            $blank->getLegalEntity(),
                            $blank->getReferenceType(),
                            $blank->getSerie(),
                            $blank->getNumber(),
                            1,
                            $blank->getLeadingZeros()
                        );
                        $this->em->persist($user);

                        if ($numBlank == $firstNum) {
                            $firstBlank = $blank;
                        }

                        if ($numBlank == $finishNum) {
                            $lastBlank = $blank;
                        }

                        $lifeLog = new BlankLifeLog();
                        $lifeLog->setBlank($blank);
                        $lifeLog->setOperationStatus($lifeLog::S_CREATE_BLANK);
                        $lifeLog->setEndStatus('new');
                        $lifeLog->setStartUser($user);
                        $lifeLog->setEndUser($user);
                        $this->em->persist($lifeLog);

                        if ($numBlank % 100 == 0) {
                            $this->em->flush();

                            $this->em->detach($blank);
                            $this->em->detach($lifeLog);
                        }
                    }

                    $this->em->flush();
                    $this->em->clear();
                    $this->em->commit();

                    $interval = $firstBlank === $lastBlank ? '['.$firstBlank->getNumber().']'
                        : '['.$firstBlank->getNumber().', '.$lastBlank->getNumber().']';

                    $message  = 'Добавлены бланки: ';
                    $message .= $blank->getLegalEntity();
                    $message .= ', ';
                    $message .= $blank->getReferenceType();
                    $message .= ', ';
                    $message .= $blank->getSerie() ? 'серия '.$blank->getSerie().', ' : '';
                    $message .= 'интервал '.$interval.', ';
                    $message .= $amount;

                    $this->addFlash('success', $message);

                    return $this->redirectToRoute('stockman_blanks_add', [
                        'lEntityId'    => $legalEntity->getId(),
                        'refTypeId'    => $referenceType->getId(),
                        'serie'        => $serie,
                        'leadingZeros' => $leadingZeros,
                    ]);
                } else {
                    $this->addFlash('danger', 'Бланки добавлялись ранее');
                }
            }
        }

        $referenceTypes = $this->em->getRepository('CommonBundle:ReferenceType')->createQueryBuilder('rt')
            ->getQuery()->getArrayResult();

        $referenceTypesIdKeys = [];
        foreach ($referenceTypes as $referenceType) {
            $referenceTypesIdKeys[$referenceType['id']] = $referenceType;
        }

        return $this->render('AppBundle:Stockman:add_blanks.html.twig', [
            'reference_types' => $referenceTypesIdKeys,
            'form'            => $form->createView(),
        ]);
    }

    /** @Route("/instock/", name="stockman_blanks_instock") */
    public function stockmanBlanksInstockAction(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('legal_entity', EntityType::class, [
            'required'      => false,
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'choice_label'  => 'nameAndShortName',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->andWhere('le.active = :active')->setParameter('active', true)
                    ;
            },
        ]);
        $fb->add('reference_type', EntityType::class, [
            'required'    => false,
            'label'       => 'Тип бланка',
            'class'       => 'CommonBundle:ReferenceType',
            'placeholder' => ' - Выберите тип бланка - ',
        ]);

        /** @var $user \KreaLab\CommonBundle\Entity\User */
        $user              = $this->getUser();
        $stockmanIntervals = $user->getIntervals();

        $legalEntities  = [];
        $referenceTypes = [];

        foreach ($this->em->getRepository('CommonBundle:ReferenceType')->findAll() as $referenceType) {
            $referenceTypes[$referenceType->getId()] = $referenceType;
        }

        foreach ($this->em->getRepository('CommonBundle:LegalEntity')->findAll() as $legalEntity) {
            $legalEntities[$legalEntity->getId()] = $legalEntity->getShortName();
        }

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->get('legal_entity')->getData();
            if ($data) {
                $legalEntity = $this->em->find('CommonBundle:LegalEntity', $data);
                if (!$legalEntity) {
                    throw $this->createNotFoundException();
                }

                $legalId = $legalEntity->getId();
                if (isset($stockmanIntervals[$legalId])) {
                    $stockmanIntervals = [$legalId => $stockmanIntervals[$legalId]];
                } else {
                    $stockmanIntervals = [];
                }
            }

            $data = $form->get('reference_type')->getData();
            if ($data) {
                $referenceType = $this->em->find('CommonBundle:ReferenceType', $data);
                if (!$referenceType) {
                    throw $this->createNotFoundException();
                }

                $refTypeId = $referenceType->getId();
                foreach ($stockmanIntervals as &$referenceTypesData) {
                    if (isset($referenceTypesData[$refTypeId])) {
                        $referenceTypesData = [$refTypeId => $referenceTypesData[$refTypeId]];
                    } else {
                        $referenceTypesData = [];
                    }
                }
            }
        }

        BlankIntervals::clear($stockmanIntervals);

        return $this->render('AppBundle:Stockman:instock.html.twig', [
            'stockman_invervals' => $stockmanIntervals,
            'reference_types'    => $referenceTypes,
            'legal_entities'     => $legalEntities,
            'filter_form'        => $form->createView(),
        ]);
    }

    /** @Route("/instock/view/", name="stockman_blanks__instock__view") */
    public function stockmanBlanksInstockViewAction(Request $request)
    {
        $legalEntity   = $request->get('legalEntity');
        $referenceType = $request->get('referenceType');
        $serie         = $request->get('serie', '-');

        $legalEntity = $this->em->find('CommonBundle:LegalEntity', $legalEntity);
        if (!$legalEntity) {
            throw $this->createNotFoundException('Legal entity is not found.');
        }

        $referenceType = $this->em->find('CommonBundle:ReferenceType', $referenceType);
        if (!$referenceType) {
            throw $this->createNotFoundException('Reference type is not found.');
        }

        $stockman  = $this->getUser(); /** @var $stockman \KreaLab\CommonBundle\Entity\User */
        $intervals = $stockman->getIntervals();
        $intervals = $intervals[$legalEntity->getId()][$referenceType->getId()][$serie];

        return $this->render('AppBundle:Stockman:view_instock.html.twig', [
            'intervals' => $intervals,
        ]);
    }

    /** @Route("/cancelled/", name="stockman_blanks_cancelled") */
    public function stockmanBlanksCancelledAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.status = :status')->setParameter('status', 'cancelledByReferenceman')
            ->andWhere('b.referenceman IS NOT NULL')
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Stockman:cancelled_blanks.html.twig', ['pagerfanta' => $pagerfanta]);
    }

    /** @Route("/undo/{id}/", name="stockman_blanks_undo") */
    public function stockmanBlanksUndoAction($id)
    {
        /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
        $blank = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.status = :status')->setParameter('status', 'cancelledByReferenceman')
            ->andWhere('b.referenceman is not null')
            ->andWhere('b.id = :id')->setParameter('id', $id)
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$blank) {
            throw $this->createNotFoundException();
        }

        $envelope = new BlankReferencemanEnvelope();
        $envelope->setReferenceman($blank->getReferenceman());
        $envelope->setFirstNum($blank->getNumber());
        $envelope->setSerie($blank->getSerie());
        $envelope->setAmount(1);
        $envelope->setStockman($this->getUser());
        $envelope->setReferenceType($blank->getReferenceType());
        $this->em->persist($envelope);

        $oldStatus    = $blank->getStatus();
        $referenceMan = $blank->getReferenceman();
        $blank->setReferencemanApplied(null);
        $blank->setStatus('appointedToReferenceman');
        $blank->setReferencemanEnvelope($envelope);
        $this->em->persist($blank);
        $this->em->flush();

        $lifeLog = new BlankLifeLog();
        $lifeLog->setBlank($blank);
        $lifeLog->setOperationStatus($lifeLog::RS_APPOINTED_TO_REFERENCE);
        $lifeLog->setEnvelopeId($envelope->getId());
        $lifeLog->setEnvelopeType('blank_referenceman_envelope');

        $lifeLog->setStartStatus($oldStatus);
        $lifeLog->setEndStatus($blank->getStatus());

        $lifeLog->setStartUser($referenceMan);
        $lifeLog->setEndUser($this->getUser());
        $this->em->persist($lifeLog);

        $this->em->flush();

        $this->addFlash('success', 'Вернули бланк справковеду');
        return $this->redirectToRoute('stockman_blanks_cancelled');
    }

    /** @Route("/delete-{id}/", name="stockman_blanks_delete") */
    public function stockmanBlanksDeleteAction($id)
    {
        /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
        $blank = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.status = :status')->setParameter('status', 'cancelledByReferenceman')
            ->andWhere('b.referenceman IS NOT NULL')
            ->andWhere('b.id = :id')->setParameter('id', $id)
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$blank) {
            throw $this->createNotFoundException();
        }

        $oldStatus    = $blank->getStatus();
        $referenceMan = $blank->getReferenceman();
        $blank->setStatus('deletedByStockman');
        $this->em->persist($blank);

        $lifeLog = new BlankLifeLog();
        $lifeLog->setBlank($blank);
        $lifeLog->setOperationStatus($lifeLog::RS_DELETED_BY_STOCKMAN);
        $lifeLog->setEnvelopeId(null);
        $lifeLog->setEnvelopeType(null);

        $lifeLog->setStartStatus($oldStatus);
        $lifeLog->setEndStatus($blank->getStatus());

        $lifeLog->setStartUser($referenceMan);
        $lifeLog->setEndUser($this->getUser());
        $this->em->persist($lifeLog);

        $this->em->flush();

        $this->addFlash('success', 'Удалили бланк');
        return $this->redirectToRoute('stockman_blanks_cancelled');
    }

    /**
     * @Route("/referenceman-stockman-envelopes/", name="stockman_blanks_referenceman_envelopes")
     */
    public function stockmanBlanksReferencemanStockmanEnvelopes(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:BlankStockmanEnvelope')->createQueryBuilder('bse')
            ->leftJoin('bse.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('bse.blanks', 'b', 'WITH', 'b.status = :status')
            ->setParameter('status', 'appointedToStockman')
            ->addSelect('b')
            ->andWhere('bse.stockman = :stockman')->setParameter('stockman', $this->getUser())
            ->andWhere('bse.stockman_applied IS NULL')
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Stockman:envelopes_from_referenceman.html.twig', [
            'pagerfanta'  => $pagerfanta,
            'filter_form' => null,
            'list_fields' => [
                ['id', 'min_col text-right'],
                'stockman',
                'referenceman',
                ['legalEntityShortName', 'min_col text-left'],
                ['referenceType', 'min_col text-left'],
                ['serie', 'min_col text-left'],
                ['amount', 'min_col text-right'],
            ],
        ]);
    }

    /** @Route("/referenceman-stockman-envelopes/view-intervals-{id}/",
     *  name="stockman_blanks__referenceman_envelope__view_intervals") */
    public function stockmanBlanksReferencemanStockmanEnvelopeViewIntervals($id)
    {
        $envelope = $this->em->find('CommonBundle:BlankStockmanEnvelope', $id);
        if (!$envelope) {
            throw $this->createNotFoundException('Нет пакета');
        }

        $referenceTypes = [];
        foreach ($this->em->getRepository('CommonBundle:ReferenceType')->findAll() as $referenceType) {
            $referenceTypes[$referenceType->getId()] = $referenceType;
        }

        $legalEntities = [];
        foreach ($this->em->getRepository('CommonBundle:LegalEntity')->findAll() as $legalEntity) {
            $legalEntities[$legalEntity->getId()] = $legalEntity;
        }

        $intervals = $envelope->getIntervals();

        return $this->render('AppBundle:Stockman:referenceman_stockman_envelope__view_intervals.html.twig', [
            'envelope'        => $envelope,
            'reference_types' => $referenceTypes,
            'legal_entities'  => $legalEntities,
            'intervals'       => $intervals,
        ]);
    }

    /** @Route("/referenceman-envelopes/get-{id}/", name="stockman_blanks_get_referenceman_envelope") */
    public function stockmanBlanksGetStockmanEnvelopeAction($id)
    {
        /** @var $stockman \KreaLab\CommonBundle\Entity\User */
        $stockman = $this->getUser();

        $envelope = $this->em->find('CommonBundle:BlankStockmanEnvelope', $id);
        if (!$envelope || $envelope->getStockman()->getId() != $stockman->getId()) {
            throw $this->createNotFoundException();
        }

        $envelope->setStockmanApplied(new \DateTime('today'));
        $this->em->persist($envelope);
        $this->em->flush();

        $blanks = $this->em->getRepository('CommonBundle:Blank')->findBy([
            'stockman_envelope' => $envelope,
        ]);

        foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $oldStatus = $blank->getStatus();
            $blank->setStatus('new');
            $blank->setStockmanApplied(new \DateTime());
            $blank->setReferencemanEnvelope(null);
            $blank->setReferenceman(null);
            $this->em->persist($blank);

            $stockman->addInterval(
                $blank->getLegalEntity(),
                $blank->getReferenceType(),
                $blank->getSerie(),
                $blank->getNumber(),
                1,
                $blank->getLeadingZeros()
            );

            $envelope->removeInterval(
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
            $lifeLog->setOperationStatus($lifeLog::RS_ACCEPT_REVERT_ENVELOP_ALL_BLANKS_FROM_REFERENCE);
            $lifeLog->setEnvelopeId($envelope->getId());
            $lifeLog->setEnvelopeType('blank_stockman_envelope');

            $lifeLog->setStartStatus($oldStatus);
            $lifeLog->setEndStatus($blank->getStatus());

            $lifeLog->setStartUser($envelope->getReferenceman());
            $lifeLog->setEndUser($this->getUser());
            $this->em->persist($lifeLog);
        }

        $this->em->persist($stockman);
        $this->em->flush();

        $this->addFlash('success', 'Приняли.');
        return $this->redirectToRoute('stockman_blanks_referenceman_envelopes');
    }

    /** @Route("/referenceman-stockman-envelopes/view-{id}/",
     *      name="stockman_blanks_view_referenceman_envelope") */
    public function stockmanBlanksReferencemanStockmanEnvelopeViewAction(Request $request, $id)
    {
        $envelope = $this->em->find('CommonBundle:BlankStockmanEnvelope', $id);
        if (!$envelope) {
            throw $this->createNotFoundException('Нет пакета');
        }

        $blanks = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.stockman_envelope = :stockman_envelopeId')->setParameter('stockman_envelopeId', $id)
            ->andWhere('b.status = :status')->setParameter('status', 'appointedToStockman')
            ->getQuery()->execute();

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);

        $blankChoices = [];
        foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $blankChoices[$blank->getId()] = $blank->getId();
        }

        $fb->add('blanks', ChoiceType::class, [
            'choices_as_values' => true,
            'multiple'          => true,
            'expanded'          => true,
            'choices'           => $blankChoices,
        ]);

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $blank    = null;
            $blankIds = $form->get('blanks')->getData();
            $stockman = $this->getUser();
            foreach ($blankIds as $blankId) {
                $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy([
                    'id'                => $blankId,
                    'stockman_envelope' => $id,
                    'status'            => 'appointedToStockman',
                ]);

                $oldStatus = $blank->getStatus();
                $blank->setStatus('new');
                $blank->setStockmanApplied(new \DateTime('today'));
                $blank->setReferencemanEnvelope(null);
                $blank->setReferenceman(null);

                $referenceman = $blank->getOldReferenceman();/** @var $referenceman \KreaLab\CommonBundle\Entity\User */

                /** @var $stockman \KreaLab\CommonBundle\Entity\User */
                $stockman->addInterval(
                    $blank->getLegalEntity(),
                    $blank->getReferenceType(),
                    $blank->getSerie(),
                    $blank->getNumber(),
                    1,
                    $blank->getLeadingZeros()
                );
                $this->em->persist($blank);

                $envelope->removeInterval(
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
                $lifeLog->setOperationStatus($lifeLog::RS_ACCEPT_REVERT_BLANK_FROM_REFERENCE);
                $lifeLog->setEnvelopeId(null);
                $lifeLog->setEnvelopeType(null);

                $lifeLog->setStartStatus($oldStatus);
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($referenceman);
                $lifeLog->setEndUser($this->getUser());
                $this->em->persist($lifeLog);
            }

            $this->em->persist($stockman);
            $this->em->flush();

            if ($blank) {
                $blanksAmount = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                    ->select('COUNT(b.id) as b_cnt')
                    ->leftJoin('b.operator_envelope', 'oe')
                    ->andWhere('b.status = :status')
                    ->setParameter('status', 'appointedToStockman')
                    ->andWhere('b.stockman_envelope = :stockman_envelope')
                    ->setParameter('stockman_envelope', $blank->getStockmanEnvelope())
                    ->andWhere('b.stockman = :stockman')->setParameter('stockman', $this->getUser())
                    ->andWhere('b.stockman_applied IS NULL')
                    ->getQuery()->getSingleScalarResult();
                ;

                if ($blanksAmount == 0) {
                    $stockmanEnvelope = $blank->getStockmanEnvelope();
                    if ($stockmanEnvelope) {
                        $stockmanEnvelope->setStockmanApplied(new \DateTime());
                        $this->em->persist($stockmanEnvelope);
                        $this->em->flush();
                    }
                }
            }

            $this->addFlash('success', 'Приняли.');
            return $this->redirectToRoute('stockman_blanks_referenceman_envelopes');
        }

        return $this->render('AppBundle:Stockman:view_envelope_from_referenceman.html.twig', [
            'form'   => $form->createView(),
            'blanks' => $blanks,
        ]);
    }

    /** @Route("/referenceman-envelopes/add-{legalEntity}-{referenceType}-{amount}∈{serie}/",
     * name="stockman_blanks__stockman_referenceman_envelope__add_reference_type_serie")
     * @Route("/referenceman-envelopes/add-{legalEntity}-{referenceType}-{amount}/",
     * name="stockman_blanks__stockman_referenceman_envelope__add_reference_type") */
    public function stockmanBlanksAddStockmanReferencemanEnvelopeAction(
        Request $request,
        $legalEntity,
        $referenceType,
        $amount,
        $serie = null
    ) {
        $legalEntity   = $this->em->getRepository('CommonBundle:LegalEntity')->find($legalEntity);
        $referenceType = $this->em->getRepository('CommonBundle:ReferenceType')->find($referenceType);
        $serie         = trim($serie);

        if (!$referenceType) {
            throw $this->createNotFoundException();
        }

        if ($referenceType->getIsSerie() && !$serie) {
            throw $this->createNotFoundException();
        }

        $maxResult = 1000;

        $fb = $this->get('form.factory')->createNamedBuilder('', FormType::class, null, [
            'translation_domain' => false,
            'csrf_protection'    => false,
        ]);
        $fb->add('blank_referenceman_envelope', EntityType::class, [
            'label'         => 'Пакет',
            'placeholder'   => ' - Новый пакет - ',
            'class'         => 'CommonBundle:BlankReferencemanEnvelope',
            'required'      => false,
            'query_builder' => function (EntityRepository $er) use ($legalEntity, $referenceType, $serie) {
                return $er->createQueryBuilder('bre')
                    ->andWhere('bre.stockman = :stockman')->setParameter('stockman', $this->getUser())
                    ->andWhere('bre.legal_entity = :legal_entity')->setParameter('legal_entity', $legalEntity)
                    ->andWhere('bre.referenceman IS NULL')
                    ->andWhere('bre.serie = :serie')->setParameter('serie', $serie)
                    ;
            },
        ]);
        $fb->add('first_num', TextType::class, [
            'label'       => 'Номер первого бланка',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
        $fb->add('amount', IntegerType::class, [
            'label'       => 'Количество',
            'attr'        => ['min' => 1, 'max' => $maxResult],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Range(['min' => 1, 'max' => $maxResult]),
            ],
        ]);
        $fb->add('referenceman', EntityType::class, [
            'label'         => 'Справковед',
            'placeholder'   => ' - Выберите справковеда - ',
            'class'         => 'CommonBundle:User',
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.active = :active')->setParameter('active', true)
                    ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_REFERENCEMAN%')
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
            $leadingZeros = strlen($form->get('first_num')->getData());

            $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->andWhere('b.status = :status')->setParameter('status', 'new')
                ->andWhere('b.referenceman_applied is null')
                ->andWhere('b.stockman = :stockman')->setParameter('stockman', $this->getUser())
                ->andWhere('b.reference_type = :reference_type')
                ->setParameter('reference_type', $referenceType)
                ->andWhere('b.legal_entity = :legal_entity')
                ->setParameter('legal_entity', $legalEntity)
                ->andWhere('b.number >= :first_num')->setParameter('first_num', $form->get('first_num')->getData())
                ->andWhere('b.referenceman_envelope is null')
                ->andWhere('b.leading_zeros = :leading_zeros')->setParameter('leading_zeros', $leadingZeros)
                ->orderBy('b.number')
                ->setMaxResults($form->get('amount')->getData())
            ;

            if ($referenceType->getIsSerie()) {
                $qb->andWhere('b.serie = :serie')->setParameter('serie', $serie);
            }

            $blanks = $qb->getQuery()->execute();

            foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
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
                'label' => 'Сформировать пакет',
                'attr'  => ['class' => 'btn btn-success pull-right'],
            ]);
            $form2 = $fb2->getForm();

            $form2->handleRequest($request);
            if ($form2->isValid()) {
                $chosenBlanks = $form2->get('blanks')->getData();

                $blanks   = $form2->get('blanks')->getData();
                $blankIds = array_values($blanks);

                $blanksValid = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                    ->andWhere('b.id IN (:ids)')->setParameter('ids', $blankIds)
                    ->andWhere('b.status = :status')->setParameter('status', 'new')
                    ->andWhere('b.referenceman_applied is null')
                    ->andWhere('b.stockman = :stockman')->setParameter('stockman', $this->getUser())
                    ->andWhere('b.reference_type = :reference_type')
                    ->setParameter('reference_type', $referenceType)
                    ->andWhere('b.legal_entity = :legal_entity')
                    ->setParameter('legal_entity', $legalEntity)
                    ->andWhere('b.number >= :first_num')->setParameter('first_num', $form->get('first_num')->getData())
                    ->andWhere('b.referenceman_envelope is null')
                    ->orderBy('b.number')
                    ->setMaxResults($request->get('amount', $maxResult))
                    ->getQuery()->getResult();

                if ($blanksValid) {
                    $this->em->beginTransaction();
                    /** @var $stockman \KreaLab\CommonBundle\Entity\User */
                    $stockman = $this->getUser();

                    $envelope = $form->get('blank_referenceman_envelope')->getData();

                    if (!$envelope) {
                        $envelope = new BlankReferencemanEnvelope();
                        $envelope->setStockman($stockman);
                        $envelope->setReferenceType($referenceType);
                        $envelope->setLegalEntity($legalEntity);
                        $envelope->setSerie($serie);

                        $firstBlankId = current($chosenBlanks);
                        $firstBlank   = $this->em->find('CommonBundle:Blank', $firstBlankId);
                        $envelope->setFirstNum($firstBlank->getNumber());
                        $envelope->setLeadingZeros($firstBlank->getLeadingZeros());
                    } else {
                        $envelope = $this->em->find('CommonBundle:BlankReferencemanEnvelope', $envelope);
                    }

                    if ($form->get('referenceman')->getData()) {
                        $envelope->setReferenceman($form->get('referenceman')->getData());
                        foreach ($envelope->getBlanks() as $blank) {
                            $blank->setReferenceman($form->get('referenceman')->getData());
                            $this->em->persist($blank);
                        }
                    }

                    $envelope->setAmount($envelope->getAmount() + count($chosenBlanks));
                    $this->em->persist($envelope);
                    $this->em->flush();

                    $cnt = 0;
                    foreach ($blanksValid as &$blank) {
                        $oldStatus = $blank->getStatus();

                        if ($form->get('referenceman')->getData()) {
                            $blank->setStatus('appointedToReferenceman');
                            $blank->setReferenceman($form->get('referenceman')->getData());
                        } else {
                            $blank->setStatus('inEnvelopeForReferenceman');
                        }

                        $blank->setReferencemanEnvelope($envelope);
                        $this->em->persist($blank);

                        $lifeLog = new BlankLifeLog();
                        $lifeLog->setBlank($blank);
                        $lifeLog->setOperationStatus($lifeLog::S_CREATE_ENVELOPE_FOR_REFERENCEMAN);
                        $lifeLog->setEnvelopeId($envelope->getId());
                        $lifeLog->setEnvelopeType('blank_referenceman_envelope');

                        $lifeLog->setStartStatus($oldStatus);
                        $lifeLog->setEndStatus($blank->getStatus());

                        $lifeLog->setStartUser($stockman);
                        $lifeLog->setEndUser($stockman);

                        $this->em->persist($lifeLog);

                        $stockman->removeInterval(
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

                        ++ $cnt;
                    }

                    $this->em->persist($envelope);
                    $this->em->persist($stockman);
                    $this->em->flush();
                    $this->em->clear();
                    $this->em->commit();

                    $this->addFlash('success', 'Сформировали');
                    return $this->redirectToRoute(
                        'stockman_blanks__stockman_referenceman_envelope__view_after_creating',
                        ['id' => $envelope->getId()]
                    );
                } else {
                    $this->addFlash('danger', 'Не выбраны бланки');
                }
            }
        }

        $info['legalEntityShortName'] = $legalEntity->getShortName();
        $info['referenceType']        = $referenceType->getName();
        $info['serie']                = $serie;
        $info['amount']               = $amount;
        $info['interval']             = '';
        $intervalsArr                 = [];
        $stockman                     = $this->getUser(); /** @var $stockman \KreaLab\CommonBundle\Entity\User */
        $stockmanIntervals            = $stockman->getIntervals();
        $intervals                    = $stockmanIntervals[$legalEntity->getId()][$referenceType->getId()][$serie];

        foreach ($intervals as $leadingZero => $curIntervals) {
            foreach ($curIntervals as $int) {
                $int[0] = str_pad($int[0], $leadingZero, '0', STR_PAD_LEFT);
                $int[1] = str_pad($int[1], $leadingZero, '0', STR_PAD_LEFT);

                $intervalsArr['interval'] = ($int[0] == $int[1]) ? '['.$int[0].']' : '['.$int[0].', '.$int[1].']';
                $intervalsArr['amount']   = $int[1] - $int[0] + 1;
                $info['intervals'][]      = $intervalsArr;
            }
        }

        return $this->render('AppBundle:Stockman:add_referenceman_envelope.html.twig', [
            'form'   => $form->createView(),
            'form2'  => $form2 ? $form2->createView() : null,
            'blanks' => $blanks,
            'info'   => $info,
        ]);
    }

    /** @Route("/stockman-referenceman-envelopes/",
     *      name="stockman_blanks__stockman_referenceman_envelopes") */
    public function stockmanBlanksStockmanReferencemanEnvelopes(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:BlankReferencemanEnvelope')->createQueryBuilder('bre')
            ->leftJoin('bre.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('bre.legal_entity', 'le')->addSelect('le')
            ->andWhere('bre.stockman = :stockman')->setParameter('stockman', $this->getUser())
            ->andWhere('bre.referenceman_applied IS NULL')
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        $referenceTypes = [];
        foreach ($this->em->getRepository('CommonBundle:ReferenceType')->findAll() as $referenceType) {
            $referenceTypes[$referenceType->getId()] = $referenceType;
        }

        $legalEntities = [];
        foreach ($this->em->getRepository('CommonBundle:LegalEntity')->findAll() as $legalEntity) {
            $legalEntities[$legalEntity->getId()] = $legalEntity;
        }

        return $this->render('AppBundle:Stockman:envelopes_for_referenceman.html.twig', [
            'pagerfanta'      => $pagerfanta,
            'reference_types' => $referenceTypes,
            'legal_entities'  => $legalEntities,
            'filter_form'     => null,
            'list_fields'     => [
                ['id', 'min_col text-right'],
                ['legalEntityShortName', 'min_col text-right'],
                'referenceType',
                ['serie', 'min_col text-left'],
                ['first_num', 'min_col text-right'],
                ['amount', 'min_col text-right'],
                ['referenceman', 'text-left'],
            ],
        ]);
    }

    /** @Route("/stockman-referenceman-envelopes/view-{id}/",
     *      name="stockman_blanks__stockman_referenceman_envelope__view") */
    public function stockmanBlanksViewStockmanReferencemanEnvelope($id)
    {
        $envelope = $this->em->getRepository('CommonBundle:BlankReferencemanEnvelope')->createQueryBuilder('bre')
            ->leftJoin('bre.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('bre.blanks', 'b')->addSelect('b')
            ->andWhere('bre.stockman = :stockman')->setParameter('stockman', $this->getUser())
            ->andWhere('bre.referenceman_applied IS NULL')
            ->andWhere('bre.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        if (!$envelope) {
            throw $this->createNotFoundException();
        }

        return $this->render('AppBundle:Stockman:view_envelope_for_referenceman.html.twig', [
            'envelope'  => $envelope,
        ]);
    }

    /** @Route("/stockman-referenceman-envelopes/send-{id}/",
     *      name="stockman_blanks__stockman_referenceman_envelope__send") */
    public function stockmanBlanksSendStockmanReferencemanEnvelopeAction(Request $request, $id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
        $envelope = $this->em->getRepository('CommonBundle:BlankReferencemanEnvelope')->createQueryBuilder('bre')
            ->andWhere('bre.stockman = :stockman')->setParameter('stockman', $this->getUser())
            ->andWhere('bre.referenceman_applied is null')
            ->andWhere('bre.referenceman is null')
            ->andWhere('bre.id = :id')->setParameter('id', $id)
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$envelope) {
            throw $this->createNotFoundException();
        }

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('referenceman', EntityType::class, [
            'label'         => 'Справковед',
            'placeholder'   => ' - Выберите справковеда - ',
            'constraints'   => new Assert\NotBlank(),
            'class'         => 'CommonBundle:User',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.active = :active')->setParameter('active', true)
                    ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_REFERENCEMAN%')
                    ->addOrderBy('u.last_name')
                    ->addOrderBy('u.first_name')
                    ->addOrderBy('u.patronymic')
                    ;
            },
        ]);
        $fb->add('save', SubmitType::class, [
            'label' => 'Передать',
            'attr'  => ['class' => 'btn-success'],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $referenceman = $form->get('referenceman')->getData();

            $envelope->setReferenceman($referenceman);
            $this->em->persist($envelope);
            $this->em->flush();

            $blanks = $this->em->getRepository('CommonBundle:Blank')->findBy([
                'referenceman_envelope' => $envelope,
                'status'                => 'inEnvelopeForReferenceman',
                'referenceman_applied'  => null,
            ]);

            $cnt = 0;
            foreach ($blanks as &$blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                $oldStatus = $blank->getStatus();
                $blank->setStatus('appointedToReferenceman');
                $blank->setReferenceman($referenceman);
                $this->em->persist($blank);

                $lifeLog = new BlankLifeLog();

                $status = $oldStatus == 'inEnvelopeForReferenceman'
                    ? $lifeLog::SR_ASSIGN_ENVELOPE_TO_REFERENCEMAN
                    : $lifeLog::SR_ASSIGN_ENVELOPE_TO_REFERENCEMAN_AGAIN;

                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($status);
                $lifeLog->setEnvelopeId($envelope->getId());
                $lifeLog->setEnvelopeType('blank_referenceman_envelope');

                $lifeLog->setStartStatus($oldStatus);
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($this->getUser());
                $lifeLog->setEndUser($referenceman);

                $this->em->persist($lifeLog);

                if ($cnt % 100 == 0) {
                    $this->em->flush();

                    $this->em->detach($blank);
                    $this->em->detach($lifeLog);
                }

                $cnt ++;
            }

            $this->em->flush();

            $this->addFlash('success', 'Передали');
            return $this->redirectToRoute('stockman_blanks__stockman_referenceman_envelopes');
        }

        $lEntityId = $envelope->getLegalEntity()->getId();
        $refTypeId = $envelope->getReferenceType()->getId();

        $info      = [];
        $intervals = $envelope->getIntervals();
        $intervals = $intervals[$lEntityId][$refTypeId][$envelope->getSerie()];

        foreach ($intervals as $leadingZero => $curIntervals) {
            foreach ($curIntervals as $int) {
                $int[0] = str_pad($int[0], $leadingZero, '0', STR_PAD_LEFT);
                $int[1] = str_pad($int[1], $leadingZero, '0', STR_PAD_LEFT);

                $intervalsArr['interval'] = ($int[0] == $int[1]) ? '['.$int[0].']' : '['.$int[0].', '.$int[1].']';
                $intervalsArr['amount']   = $int[1] - $int[0] + 1;
                $info['intervals'][]      = $intervalsArr;
            }
        }

        return $this->render('AppBundle:Stockman:send_envelope_to_referenceman.html.twig', [
            'form'     => $form->createView(),
            'envelope' => $envelope,
            'info'     => $info,
        ]);
    }

    /** @Route("/stockman-referenceman-envelopes/remove-referenceman-{id}/",
     *      name="stockman_blanks__stockman_referenceman_envelope__remove_referenceman") */
    public function stockmanBlanksStockmanReferencemanEnvelopeRemoveOperator($id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
        $envelope = $this->em->getRepository('CommonBundle:BlankReferencemanEnvelope')->createQueryBuilder('bre')
            ->leftJoin('bre.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('bre.blanks', 'b')->addSelect('b')
            ->andWhere('bre.stockman = :stockman')->setParameter('stockman', $this->getUser())
            ->andWhere('bre.referenceman_applied is null')
            ->andWhere('bre.referenceman is not null')
            ->andWhere('bre.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        if ($envelope) {
            $oldReferenceman = $envelope->getReferenceman();
            $envelope->setReferenceman(null);
            $blanks = $envelope->getBlanks();

            $cnt = 0;
            foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                $blank->setStatus('inEnvelopeForReferenceman');
                $blank->setReferenceman(null);
                $this->em->persist($blank);

                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::SR_REMOVE_REFERENCEMAN_FROM_ENVELOPE);
                $lifeLog->setEnvelopeId($envelope->getId());
                $lifeLog->setEnvelopeType('blank_referenceman_envelope');

                $lifeLog->setStartStatus($blank->getStatus());
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($oldReferenceman);
                $lifeLog->setEndUser($this->getUser());

                $this->em->persist($lifeLog);

                if ($cnt % 100 == 0) {
                    $this->em->flush();
                    $this->em->detach($lifeLog);
                }

                $cnt ++;
            };

            $this->em->persist($envelope);
            $this->em->flush();
        }

        return $this->redirectToRoute('stockman_blanks__stockman_referenceman_envelopes');
    }

    /** @Route("/stockman-referenceman-envelopes/remove-envelope-{id}/",
     *      name="stockman_blanks__stockman_referenceman_envelope__remove") */
    public function stockmanBlanksStockmanReferencemanEnvelopeRemoveEnvelope($id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
        $envelope = $this->em->getRepository('CommonBundle:BlankReferencemanEnvelope')->createQueryBuilder('bre')
            ->leftJoin('bre.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('bre.blanks', 'b')->addSelect('b')
            ->andWhere('bre.stockman = :stockman')->setParameter('stockman', $this->getUser())
            ->andWhere('bre.referenceman_applied is null')
            ->andWhere('bre.referenceman is null')
            ->andWhere('bre.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        if (!$envelope) {
            throw $this->createNotFoundException('no envelope');
        }

        if ($envelope) {
            $blanks = $envelope->getBlanks();
            foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                $oldStatus = $blank->getStatus();
                $blank->setOperatorEnvelope(null);
                $blank->setStatus('new');
                $this->em->persist($blank);

                /** @var $referenceman \KreaLab\CommonBundle\Entity\User */
                $stockman = $this->getUser();
                $stockman->addInterval(
                    $blank->getLegalEntity(),
                    $blank->getReferenceType(),
                    $blank->getSerie(),
                    $blank->getNumber(),
                    1,
                    $blank->getLeadingZeros()
                );
                $this->em->persist($stockman);

                $this->em->flush();

                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::RR_DELETE_ENVELOP_BY_REFERENCE);
                $lifeLog->setEnvelopeId($envelope->getId());
                $lifeLog->setEnvelopeType('blank_referenceman_envelope');

                $lifeLog->setStartStatus($oldStatus);
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($this->getUser());
                $lifeLog->setEndUser($this->getUser());

                $this->em->persist($lifeLog);
            };

            $this->em->remove($envelope);
            $this->em->flush();
        }

        return $this->redirectToRoute('stockman_blanks__stockman_referenceman_envelopes');
    }

    /** @Route("/stockman-referenceman-envelopes/delete-blank-from-envelope-{id}/",
     *      name="stockman_blanks__stockman_referenceman_envelope__delete_blank") */
    public function stockmanBlanksDeleteBlankFromStockmanReferencemanEnvelope($id)
    {
        /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
        $blank = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.id = :id')->setParameter('id', $id)
            ->andWhere('b.stockman = :stockman')->setParameter('stockman', $this->getUser())
            ->andWhere('b.status = :status')
            ->setParameter('status', 'inEnvelopeForReferenceman')
            ->andWhere('b.referenceman_applied IS NULL')
            ->andWhere('b.referenceman IS NULL')
            ->getQuery()->getOneOrNullResult();
        if (!$blank) {
            throw $this->createNotFoundException();
        }

        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
        $envelope     = $blank->getReferencemanEnvelope();
        $referenceman = $envelope->getReferenceman();
        $oldStatus    = $blank->getStatus();

        $envelope->removeInterval(
            $blank->getLegalEntity(),
            $blank->getReferenceType(),
            $blank->getSerie(),
            $blank->getNumber(),
            1,
            $blank->getLeadingZeros()
        );

        /** @var $stockman \KreaLab\CommonBundle\Entity\User */
        $stockman = $this->getUser();
        $stockman->addInterval(
            $blank->getLegalEntity(),
            $blank->getReferenceType(),
            $blank->getSerie(),
            $blank->getNumber(),
            1,
            $blank->getLeadingZeros()
        );

        $envelope->removeBlank($blank);
        $envelope->setAmount($envelope->getAmount() - 1);

        $blank->setReferencemanEnvelope(null);

        $blank->setStatus('new');

        $this->em->persist($envelope);
        $this->em->persist($blank);

        $lifeLog = new BlankLifeLog();
        $lifeLog->setBlank($blank);
        $lifeLog->setOperationStatus($lifeLog::RR_REMOVE_BLANK_IN_ENVELOP_FOR_OPERATOR);
        $lifeLog->setEnvelopeId(null);
        $lifeLog->setEnvelopeType(null);

        $lifeLog->setStartStatus($oldStatus);
        $lifeLog->setEndStatus($blank->getStatus());

        $lifeLog->setStartUser($referenceman);
        $lifeLog->setEndUser($stockman);

        $this->em->persist($lifeLog);

        $this->em->flush();

        return $this->redirectToRoute('stockman_blanks__stockman_referenceman_envelope__view', [
            'id' => $envelope->getId()
        ]);
    }

    /** @Route("/stockman-cancelled/add-{legalEntity}-{referenceType}/",
     *      name="stockman_blanks__add_stockman_cancelled")
     * @Route("/stockman-cancelled/add-{legalEntity}-{referenceType}∈{serie}/",
     *      name="stockman_blanks__add_stockman_cancelled__serie") */
    public function referencemanBlanksAddReferencemanCancelledAction(
        Request $request,
        $legalEntity,
        $referenceType,
        $serie = null
    ) {
        if ($serie == '-') {
            $serie = null;
        }

        $lEntity = $this->em->getRepository('CommonBundle:LegalEntity')->findOneBy([
            'id'     => $legalEntity,
            'active' => true,
        ]);

        if (!$lEntity) {
            throw $this->createNotFoundException('Legal entity not found');
        }

        $rType = $this->em->find('CommonBundle:ReferenceType', $referenceType);

        if (!$rType) {
            throw $this->createNotFoundException('Reference type not found');
        }

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('number', TextType::class, [
            'label'       => 'Номер бланка',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $criteria = [
                'number'         => (int)$form->get('number')->getData(),
                'reference_type' => $rType,
                'legal_entity'   => $lEntity,
                'status'         => 'new',
                'stockman'       => $this->getUser(),
                'leading_zeros'  => strlen($form->get('number')->getData()),
            ];

            if (!empty($serie) and $serie != '-') {
                $criteria['serie'] = $serie;
            }

            $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy($criteria);

            if ($blank) {
                $oldStatus = $blank->getStatus();
                $blank->setStatus('cancelledByStockman');
                $this->em->persist($blank);
                $user = $this->getUser(); /** @var $user \KreaLab\CommonBundle\Entity\User */
                if (!empty($user->getIntervals())) {
                    $user->removeInterval(
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
                $lifeLog->setOperationStatus($lifeLog::S_ADDED_NOT_FOUND_BLANK);
                $lifeLog->setEnvelopeId(null);
                $lifeLog->setEnvelopeType(null);

                $lifeLog->setStartStatus($oldStatus);
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($user);
                $lifeLog->setEndUser($user);

                $this->em->persist($lifeLog);

                $this->em->flush();

                $message  = $blank->getLegalEntity().', ';
                $message .= $blank->getReferenceType().', ';
                $message .= $blank->getSerie().', ';
                $message .= $blank->getNumber().'.';

                $this->addFlash('success', 'Ненайденный бланк добавлен. '.$message);
                return $this->redirectToRoute('stockman_blanks_instock');
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

        return $this->render('AppBundle:Stockman:cancel_blank.html.twig', [
            'reference_types' => $referenceTypesIdKeys,
            'form'            => $form->createView(),
        ]);
    }

    /**
     * @Route("/stockman-cancelled/", name="stockman_blanks__stockman_cancelled")
     * */
    public function referencemanBlanksReferencemanCancelledAction(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);

        $fb->add('start_time', DateType::class, [
            'label'    => 'Дата начала',
            'required' => false,
        ]);
        $fb->add('end_time', DateType::class, [
            'label'    => 'Дата окончания',
            'required' => false,
        ]);

        $fb->add('legal_entity', EntityType::class, [
            'required'      => false,
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'choice_label'  => 'nameAndShortName',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->andWhere('le.active = :active')->setParameter('active', true)
                    ;
            },
        ]);

        $fb->add('is_my_blank', CheckboxType::class, [
            'required' => false,
            'label'    => 'Только свои',
        ]);

        $fb->add('referenceman', EntityType::class, [
            'label'         => 'Справковед',
            'required'      => false,
            'class'         => 'CommonBundle:User',
            'placeholder'   => ' - Выберите справковеда - ',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_REFERENCEMAN%')
                    ->addOrderBy('u.last_name')
                    ->addOrderBy('u.first_name')
                    ->addOrderBy('u.patronymic');
            },
        ]);

        $fb->add('reference_type', EntityType::class, [
            'required'    => false,
            'label'       => 'Тип бланка',
            'class'       => 'CommonBundle:ReferenceType',
            'placeholder' => ' - Выберите тип бланка - ',
        ]);

        $fb->add('number', TextType::class, [
            'required' => false,
            'label'    => 'Номер бланка',
        ]);

        $form = $fb->getForm();
        $form->handleRequest($request);

        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->leftJoin('b.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('b.legal_entity', 'le')->addSelect('le')
            ->orderBy('b.updated_at', 'desc')

        ;

        $queryString    = null;
        $queryStringArr = [];
        $statuses[]     = 'cancelledByStockman';
        $statuses[]     = 'deletedByStockman';

        if ($form->isValid()) {
            $data = null;
            $data = $form->get('start_time')->getData();
            if ($data) {
                $qb->andWhere('b.updated_at >= :start_time')->setParameter('start_time', $data);
                $queryStringArr[] = 'дата начала: '.$data->format('Y-m-d');
            }

            $data = $form->get('end_time')->getData();
            if ($data) {
                $qb->andWhere('b.updated_at <= :end_time')->setParameter('end_time', $data);
                $queryStringArr[] = 'дата окончания: '.$data->format('Y-m-d');
            }

            $data = $form->get('legal_entity')->getData();
            if ($data) {
                $qb->andWhere('b.legal_entity = :legal_entity')->setParameter('legal_entity', $data);
                $queryStringArr[] = 'юрлицо: '.$data;
            }

            $data = $form->get('reference_type')->getData();
            if ($data) {
                $qb->andWhere('b.reference_type = :reference_type')->setParameter('reference_type', $data);
                $queryStringArr[] = 'тип бланка: '.$data;
            }

            $data = $form->get('referenceman')->getData();
            if ($data) {
                $qb->andWhere('b.referenceman = :referenceman')->setParameter('referenceman', $data);
                $queryStringArr[] = 'справковед: '.$data;
            }

            $data = $form->get('is_my_blank')->getData();
            if ($data) {
                $qb->andWhere('b.stockman = :stockman')->setParameter('stockman', $this->getUser());
                $queryStringArr[] = 'только свои';
                $statuses         = [];
                $statuses[]       = 'cancelledByStockman';
            }

            $data = $form->get('number')->getData();
            if ($data) {
                $qb->andWhere('b.leading_zeros = :leading_zeros')->setParameter('leading_zeros', strlen($data));
                $qb->andWhere('b.number = :number')->setParameter('number', $data);
                $queryStringArr[] = 'номер бланка: '.$data;
            }
        }

        $qb->andWhere('b.status IN (:statuses)')->setParameter('statuses', $statuses);

        $queryString = implode(', ', $queryStringArr);

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Stockman:cancelled_blanks_by_stockman.html.twig', [
            'pagerfanta'   => $pagerfanta,
            'filter_form'  => $form->createView(),
            'query_string' => $queryString,
            'list_fields'  => [
                ['updatedAt', 'min_col text-left'],
                ['legalEntityShortName', 'min_col text-right'],
                ['reference_type', 'min_col text-left'],
                ['serie', 'min_col text-left'],
                ['number', 'min_col text-right'],
                ['referenceman', 'min_col text-left'],
            ],
        ]);
    }
}
