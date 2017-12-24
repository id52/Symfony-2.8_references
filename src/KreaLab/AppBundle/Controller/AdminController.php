<?php

namespace KreaLab\AppBundle\Controller;

use Doctrine\ORM\EntityRepository;
use KreaLab\CommonBundle\Entity\Agreement;
use KreaLab\CommonBundle\Entity\BlankLifeLog;
use KreaLab\CommonBundle\Entity\Service;
use KreaLab\CommonBundle\Entity\Workplace;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class AdminController extends Controller
{
    /** @var $em \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    }

    /**
     * @Route("/services-rendered/", name="services_rendered")
     * @Template("AppBundle:Admin:services_rendered.html.twig")
     */
    public function servicesRenderedAction(Request $request)
    {
        $pagerfanta = null;
        $cashboxes  = null;
        $workplaces = null;
        $total      = 0;

        $fb = $this->createFormBuilder([
            'csrf_protection'    => false,
            'translation_domain' => false,
        ]);
        $fb->add('start_time', DateTimeType::class, [
            'label'    => 'Время (от)',
            'required' => false,
        ]);
        $fb->add('end_time', DateTimeType::class, [
            'label'    => 'Время (до)',
            'required' => false,

        ]);
        $fb->add('service', EntityType::class, [
            'label'         => 'Услуга',
            'required'      => false,
            'class'         => 'CommonBundle:Service',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('s')->addOrderBy('s.name');
            },
        ]);
        $fb->add('filial', EntityType::class, [
            'label'         => 'Филиал',
            'required'      => false,
            'class'         => 'CommonBundle:Filial',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('f')->addOrderBy('f.name');
            },
        ]);
        $fb->add('workplace', EntityType::class, [
            'label'         => 'Рабочее место',
            'required'      => false,
            'class'         => 'CommonBundle:Workplace',
            'choice_label'  => 'name',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('w')
                    ->leftJoin('w.filial', 'f')
                    ->addOrderBy('f.name')
                    ->addOrderBy('w.name');
            },
        ]);
        $fb->add('cashbox', EntityType::class, [
            'label'         => 'Касса',
            'required'      => false,
            'class'         => 'CommonBundle:Cashbox',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->leftJoin('c.workplace', 'w')
                    ->addOrderBy('w.name')
                    ->addOrderBy('c.num');
            },
        ]);
        $fb->add('operator', EntityType::class, [
            'label'         => 'Оператор',
            'required'      => false,
            'class'         => 'CommonBundle:User',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_OPERATOR%')
                    ->addOrderBy('u.last_name')
                    ->addOrderBy('u.first_name')
                    ->addOrderBy('u.patronymic');
            },
        ]);
        $fb->add('eeg_conclusion', TextType::class, [
            'label'    => 'Заключение ЭЭГ',
            'required' => false,
        ]);

        $fb->setMethod('get');
        $filterForm = $fb->getForm();
        $filterForm->handleRequest($request);

        if ($filterForm->isValid()) {
            $qb = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
                ->leftJoin('sl.service', 's')->addSelect('s')
                ->leftJoin('sl.workplace', 'w')->addSelect('w')
                ->leftJoin('sl.cashbox', 'c')->addSelect('c')
                ->leftJoin('w.filial', 'f')->addSelect('f')
                ->leftJoin('sl.operator', 'o')->addSelect('o')
                ->addOrderBy('sl.created_at');

            $data = null;

            $data = $filterForm->get('start_time')->getData();
            if ($data) {
                $qb->andWhere('sl.created_at >= :start_time')->setParameter('start_time', $data);
            }

            $data = $filterForm->get('end_time')->getData();
            if ($data) {
                $qb->andWhere('sl.created_at <= :end_time')->setParameter('end_time', $data);
            }

            $data = $filterForm->get('service')->getData();
            if ($data) {
                $qb->andWhere('sl.service = :service')->setParameter('service', $data);
            }

            $data = $filterForm->get('filial')->getData();
            if ($data) {
                $qb->andWhere('f = :filial')->setParameter('filial', $data);
            }

            $data = $filterForm->get('workplace')->getData();
            if ($data) {
                $qb->andWhere('w = :workplace')->setParameter('workplace', $data);
            }

            $data = $filterForm->get('cashbox')->getData();
            if ($data) {
                $qb->andWhere('c = :cashbox')->setParameter('cashbox', $data);
            }

            $data = $filterForm->get('operator')->getData();
            if ($data) {
                $qb->andWhere('sl.operator = :operator')->setParameter('operator', $data);
            }

            $data = $filterForm->get('eeg_conclusion')->getData();
            if ($data) {
                $qb->andWhere('sl.eeg_conclusion LIKE :eeg_conclusion')->setParameter('eeg_conclusion', '%'.$data.'%');
            }

            $filials    = $this->em->getRepository('CommonBundle:Filial')->findBy([], ['name' => 'ASC']);
            $workplaces = [];
            $cashboxes  = [];
            foreach ($filials as $filial) {
                /** @var $filial \KreaLab\CommonBundle\Entity\Filial */
                $workplaces[$filial->getId()] = [];
                foreach ($filial->getWorkplaces() as $workplace) {
                    /** @var $workplace \KreaLab\CommonBundle\Entity\Workplace */

                    $workplaces[$filial->getId()][] = $workplace->getId();
                    $cashboxes[$workplace->getId()] = [];
                    foreach ($workplace->getCashboxes() as $cashbox) {
                        $cashboxes[$workplace->getId()][] = $cashbox->getId();
                    }
                }
            }

            $qb2 = clone $qb;
            $qb2->addSelect('sum(sl.sum)');
            $result = $qb2->getQuery()->getResult();
            $total  = $result[0][1];

            $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb, true, false));
            $pagerfanta->setMaxPerPage(100);
            $pagerfanta->setCurrentPage($request->get('page', 1));
        }

        return [
            'pagerfanta'  => $pagerfanta,
            'filter_form' => $filterForm->createView(),
            'cashboxes'   => $cashboxes,
            'workplaces'  => $workplaces,
            'total'       => $total,
        ];
    }

    /**
     * @Route("/operators-logs/", name="operators_logs")
     * @Template("AppBundle:Admin:operators_logs.html.twig")
     */
    public function operatorsLogsAction(Request $request)
    {
        $types = [
            'login',
            'logout',
            'login_attempt',
        ];
        $qb    = $this->em->getRepository('CommonBundle:ActionLog')->createQueryBuilder('al')
            ->andWhere('al.action_type IN (:types)')->setParameter('types', $types)
            ->addOrderBy('al.created_at', 'DESC');

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb, true, false));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        $aLogs = [];
        $logs  = $pagerfanta->getCurrentPageResults();
        foreach ($logs as $log) {
            /** @var $log \KreaLab\CommonBundle\Entity\ActionLog */
            $params     = $log->getParams();
            $actionType = '';
            $comment    = '';
            switch ($log->getActionType()) {
                case 'login':
                    $actionType = 'Вход';
                    $workplace  = null;
                    if (isset($params['workplace'])) {
                        $workplace = $this->em->find('CommonBundle:Workplace', $params['workplace']);
                        if ($workplace) {
                            $comment .= 'Филиал: '.$workplace->getFilial()->getName().'<br>';
                            $comment .= 'Рабочее место: '.$workplace->getName().'<br>';
                        }
                    }

                    if (!$workplace && isset($params['ip'])) {
                        $comment .= 'IP: '.$params['ip'].'<br>';
                    }
                    break;
                case 'logout':
                    $actionType = 'Выход';
                    if (isset($params['ip'])) {
                        $comment .= 'IP: '.$params['ip'].'<br>';
                    }

                    if (isset($params['reason'])) {
                        $reason = '';
                        switch ($params['reason']) {
                            case 'workplace_not_found':
                                $reason = 'не найдено рабочее место';
                                break;
                            case 'autologout':
                                $reason = 'автоматический выход';
                                break;
                            case 'login_in_other_place':
                                $reason = 'вход в другом месте';
                                break;
                            case 'bad_ip':
                                $reason = 'неверный IP';
                                break;
                        }

                        if ($reason) {
                            $comment .= 'Причина: '.$reason;
                        }
                    }
                    break;
                case 'login_attempt':
                    $actionType = 'Попытка входа';
                    if (isset($params['ip'])) {
                        $comment .= 'IP: '.$params['ip'].'<br>';
                    }

                    if (isset($params['reason'])) {
                        $reason = '';
                        switch ($params['reason']) {
                            case 'no_filial_schedule':
                                $reason = 'Нет расписания филиала';
                                break;
                            case 'no_operator_schedule':
                                $reason = 'Нет расписания оператора';
                                break;
                        }

                        if ($reason) {
                            $comment .= 'Причина: '.$reason;
                        }
                    }
                    break;
            }

            $aLogs[] = [
                'created_at'  => $log->getCreatedAt()->format('Y-m-d H:i'),
                'action_type' => $actionType,
                'user'        => $log->getUser()->getShortName(),
                'comment'     => $comment,
            ];
        }

        return [
            'pagerfanta' => $pagerfanta,
            'a_logs'     => $aLogs,
        ];
    }

    /**
     * @Route("/archivarius-logs/", name="archivarius_logs")
     * @Template("AppBundle:Admin:archivarius_logs.html.twig")
     */
    public function archivariusLogsAction(Request $request)
    {
        $types = [
            'archivarius_search',
            'archivarius_view',
            'archivarius_pdf',
        ];
        $qb    = $this->em->getRepository('CommonBundle:ActionLog')->createQueryBuilder('al')
            ->andWhere('al.action_type IN (:types)')->setParameter('types', $types)
            ->addOrderBy('al.created_at', 'DESC');

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb, true, false));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        $aLogs = [];
        $logs  = $pagerfanta->getCurrentPageResults();
        foreach ($logs as $log) {
            /** @var $log \KreaLab\CommonBundle\Entity\ActionLog */
            $params     = $log->getParams();
            $actionType = '';
            $comment    = '';
            switch ($log->getActionType()) {
                case 'archivarius_search':
                    $actionType = 'Поиск';
                    if (isset($params['search_type'])) {
                        switch ($params['search_type']) {
                            case 'fio':
                                $actionType = $actionType.' по ФИО и дате рождения';
                                $comment    = $comment.'Фамилия: '.$params['last_name'].'<br>';
                                $comment    = $comment.'Имя: '.$params['first_name'].'<br>';
                                $comment    = $comment.'Отчество: '.$params['patronymic'].'<br>';
                                $comment    = $comment.'Дата рождения: '.$params['birthday'].'<br>';
                                break;
                            case 'date':
                                $actionType = $actionType.' по номеру бланка и дате выдачи';
                                $comment    = $comment.'Номер бланка: '.$params['num_blank'].'<br>';
                                $comment    = $comment.'Дата выдачи: '.$params['date_giving'].'<br>';
                                break;
                            case 'legal':
                                $actionType = $actionType.' по номеру бланка и юридическому лицу';
                                $comment    = $comment.'Номер бланка: '.$params['num_blank'].'<br>';
                                $lEntityId  = isset($params['legal_entity']) ? $params['legal_entity'] : 0;
                                $lEntity    = $this->em->find('CommonBundle:LegalEntity', $lEntityId);
                                $comment   .= $lEntity ? ('Юридическое лицо: '.$lEntity->getName().'<br>') : '';
                                break;
                        }
                    }
                    break;
                case 'archivarius_view':
                    $actionType = 'Просмотр операции';
                    if (isset($params['log'])) {
                        $slog = $this->em->find('CommonBundle:ServiceLog', $params['log']);
                        if ($slog) {
                            $actionType .= ' №'.$slog->getId();
                        }
                    }
                    break;
                case 'archivarius_pdf':
                    $actionType = 'Открытие PDF';
                    if (isset($params['log'])) {
                        $slog = $this->em->find('CommonBundle:ServiceLog', $params['log']);
                        if ($slog) {
                            $actionType .= ' для операции №'.$slog->getId();
                        }
                    }
                    break;
            }

            $aLogs[] = [
                'created_at'  => $log->getCreatedAt()->format('Y-m-d H:i'),
                'action_type' => $actionType,
                'user'        => $log->getUser()->getShortName(),
                'comment'     => $comment,
            ];
        }

        return [
            'pagerfanta' => $pagerfanta,
            'a_logs'     => $aLogs,
        ];
    }

    /** @Route("/admin/lost/", name="admin_lost") */
    public function lostListAction(Request $request)
    {
        $formFactory = $this->container->get('form.factory');
        /** @var $fb \Symfony\Component\Form\FormBuilder */
        $fb = $formFactory->createNamedBuilder('', FormType::class, [], [
            'csrf_protection'    => false,
            'translation_domain' => false,
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
        $fb->add('reference_types', EntityType::class, [
            'label'    => 'Типы бланков',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'class'    => 'CommonBundle:ReferenceType',
            'data'     => $this->em->getRepository('CommonBundle:ReferenceType')->findAll(),
        ]);
        $fb->setMethod('get');
        $filterForm = $fb->getForm();

        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b');
        $qb->select('o.id, o.last_name, o.first_name, o.patronymic');
        $qb->addSelect('SUM(CASE WHEN b.penalty_date IS NULL THEN 1 ELSE 0 END) AS cnt_not_penalty');
        $qb->addSelect('SUM(CASE WHEN b.penalty_date IS NOT NULL THEN 1 ELSE 0 END) AS cnt_penalty');
        $qb->addSelect('SUM(b.penalty_sum) AS sum_penalty');
        $qb->leftJoin('b.operator', 'o');
        $qb->andWhere('b.status = :status')->setParameter('status', 'lostChecked');
        $qb->groupBy('o.id');

        $filterForm->handleRequest($request);

        $data = $filterForm->get('last_name')->getData();
        if ($data) {
            $qb->andWhere('o.last_name LIKE :last_name')->setParameter('last_name', '%'.$data.'%');
        }

        $data = $filterForm->get('first_name')->getData();
        if ($data) {
            $qb->andWhere('o.first_name LIKE :first_name')->setParameter('first_name', '%'.$data.'%');
        }

        $data = $filterForm->get('patronymic')->getData();
        if ($data) {
            $qb->andWhere('o.patronymic LIKE :patronymic')->setParameter('patronymic', '%'.$data.'%');
        }

        $data = $filterForm->get('reference_types')->getData();
        $qb->andWhere('b.reference_type IN (:reference_types)')->setParameter('reference_types', $data);

        $operators = $qb->getQuery()->execute();

        return $this->render('AppBundle:Admin:lost_list.html.twig', [
            'filter_form' => $filterForm->createView(),
            'operators'   => $operators,
        ]);
    }

    /** @Route("/admin/lost/view-{operatorId}/", name="admin_lost_view") */
    public function lostViewAction($operatorId)
    {
        $operator = $this->em->find('CommonBundle:User', $operatorId);
        if (!$operator) {
            throw $this->createNotFoundException();
        }

        $notPenaltyBlanks = $this->em->getRepository('CommonBundle:Blank')
            ->getLostAndNotPenaltyBlanksByOperator($operator);

        $penaltyBlanksByDate = $this->em->getRepository('CommonBundle:Blank')
            ->getLostAndPenaltyBlanksByOperator($operator, true);

        return $this->render('AppBundle:Admin:lost_view.html.twig', [
            'operator'               => $operator,
            'not_penalty_blanks'     => $notPenaltyBlanks,
            'penalty_blanks_by_date' => $penaltyBlanksByDate,
        ]);
    }

    /** @Route("/admin/lost/set-sum-ajax/", name="admin_lost_set_sum_ajax") */
    public function lostSetSumAjaxAction(Request $request)
    {
        if (!$request->isXmlHttpRequest() || !$request->isMethod('post')) {
            throw $this->createNotFoundException();
        }

        $operator = $this->em->find('CommonBundle:User', intval($request->get('operatorId')));
        if (!$operator) {
            throw $this->createNotFoundException();
        }

        /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
        $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy([
            'id'       => intval($request->get('id')),
            'status'   => 'lostChecked',
            'operator' => $operator,
        ]);
        if (!$blank) {
            throw $this->createNotFoundException();
        }

        $result = [];

        $sum = intval(trim($request->get('sum')));
        if ($sum > 0) {
            $blank->setPenaltySum($sum);
            $blank->setPenaltyDate(new \DateTime());
            $blank->setPenaltyAdmin($this->getUser());
            $this->em->persist($blank);

            $operator  = $blank->getOperator();
            $workplace = $operator->getWorkplace();

            /** @var $env \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
            $env = $blank->getOperatorEnvelope();

            $lifeLog = new BlankLifeLog();
            $lifeLog->setBlank($blank);
            $lifeLog->setOperationStatus($lifeLog::AO_SET_PENALTY_FOR_OPERATOR);
            $lifeLog->setPenaltySum($sum);
            $lifeLog->setWorkplace($workplace);
            $lifeLog->setEnvelopeId($env->getId());
            $lifeLog->setEnvelopeType('blank_operator_envelope');

            $lifeLog->setStartStatus($blank->getStatus());
            $lifeLog->setEndStatus($blank->getStatus());

            $lifeLog->setStartUser($this->getUser());
            $lifeLog->setEndUser($operator);

            $this->em->persist($lifeLog);

            $this->em->flush();

            $penaltyBlanksByDate = $this->em->getRepository('CommonBundle:Blank')
                ->getLostAndPenaltyBlanksByOperator($operator, true);

            $result['html'] = $this->renderView('@App/Admin/_lost_penalty_block.html.twig', [
                'penalty_blanks_by_date' => $penaltyBlanksByDate,
            ]);
        } else {
            $result['error'] = 'Неверное значение.';
        }

        return new JsonResponse($result);
    }

    /** @Route("/shift-logs/", name="admin_shift_logs") */
    public function getList(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);

        $fb->add('startTime', DateType::class, [
            'label'    => 'Дата от',
            'required' => false,
        ]);
        $fb->add('endTime', DateType::class, [
            'label'    => 'Дата до',
            'required' => false,
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

        $form = $fb->getForm();
        $form->handleRequest($request);

        $qb = $this->em->getRepository('CommonBundle:ShiftLog')->createQueryBuilder('sl')
            ->select('distinct sl')
            ->andWhere('sl.startTime IS NOT NULL')
            ->andWhere('sl.endTime IS NOT NULL')
            ->andWhere('sl.closed = :closed')->setParameter('closed', true)
            ->groupBy('sl.date, sl.user, sl.closed, sl.filial')
        ;

        if ($form->isValid()) {
            $data = null;
            $data = $form->get('startTime')->getData();
            if ($data) {
                $qb->andWhere('sl.date >= :startTime')->setParameter('startTime', $data);
            }

            $data = $form->get('endTime')->getData();
            if ($data) {
                $qb->andWhere('sl.date <= :endTime')->setParameter('endTime', $data);
            }

            $data = $form->get('operator')->getData();
            if ($data) {
                $qb->andWhere('sl.user = :operator')->setParameter('operator', $data);
            }
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb, true, false));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        $shiftLogsArr = [];
        foreach ($pagerfanta->getCurrentPageResults() as $pageResult) {
            /** @var $pageResult \KreaLab\CommonBundle\Entity\ShiftLog */

            $sl = $this->em->getRepository('CommonBundle:ShiftLog')->createQueryBuilder('sl')
                ->select('group_concat(sl.startTime, \',\', sl.endTime SEPARATOR \';\')')
                ->andWhere('sl.startTime IS NOT NULL')
                ->andWhere('sl.endTime IS NOT NULL')
                ->andWhere('sl.closed = :closed')->setParameter('closed', true)
                ->andWhere('sl.user = :user')->setParameter('user', $pageResult->getUser())
                ->andWhere('sl.date = :date')->setParameter('date', $pageResult->getDate())
                ->groupBy('sl.date, sl.user, sl.closed')
                ->getQuery()->getSingleScalarResult();

            $periods  = explode(';', $sl);
            $interval = 0;
            $periods3 = [];
            foreach ($periods as $period) {
                $periods2 = explode(',', $period);
                if (!empty($periods2[0]) and !empty($periods2[1])) {
                    $startTime  = new \DateTime($periods2[0]);
                    $endTime    = new \DateTime($periods2[1]);
                    $diff       = $endTime->diff($startTime);
                    $diff       = 86400 * $diff->d + 3600 * $diff->h + 60 * $diff->i + $diff->s;
                    $interval   = $interval + $diff;
                    $periods3[] = $periods2;
                }
            }

            if ($periods3) {
                $shiftLogsArr[] = [
                    'periods'  => $periods3,
                    'date'     => $pageResult->getDate(),
                    'user'     => $pageResult->getUser(),
                    'interval' => date('H:i', $interval - date('Z', 0)),
                ];
            }
        }

        return $this->render('AppBundle:Admin:shift_logs.html.twig', [
            'pagerfanta'  => $pagerfanta,
            'filter_form' => $form->createView(),
            'shiftLogs'   => $shiftLogsArr,
        ]);
    }

    /** @Route("/agreements-{workplace}/", name="admin_agreements") */
    public function agreementsAction(Workplace $workplace)
    {
        $services = $this->em->getRepository('CommonBundle:Service')->createQueryBuilder('s')
            ->leftJoin('s.agreements', 'a', 'WITH', 'a.workplace = :workplace')->addSelect('a')
            ->setParameter('workplace', $workplace)
            ->addOrderBy('s.active', 'DESC')
            ->addOrderBy('s.position', 'ASC')
            ->getQuery()->getResult();

        return $this->render('AppBundle:Admin/Agreement:list.html.twig', [
            'services'  => $services,
            'workplace' => $workplace,
        ]);
    }

    /** @Route("/agreements-{workplace}/edit-{service}/", name="admin_agreement_edit_type") */
    public function agreementEditTypeAction(Request $request, Workplace $workplace, Service $service)
    {
        /** @var $agreement \KreaLab\CommonBundle\Entity\Agreement */
        $agreement = $service->getAgreementByWorkplace($workplace);
        if (!$agreement) {
            $agreement = new Agreement();
        }

        $fb = $this->createFormBuilder($agreement, [
            'translation_domain' => false,
        ]);
        $fb->add('type', ChoiceType::class, [
            'required'          => false,
            'label'             => 'Тип договора',
            'placeholder'       => ' - Выберите тип договора - ',
            'choices_as_values' => true,
            'choices'           => array_flip([
                'bilateral'  => 'Двусторонний договор',
                'tripartite' => 'Трехсторонний договор',
            ]),
        ]);
        $fb->add('guarantor', EntityType::class, [
            'required'     => false,
            'label'        => 'Поручитель',
            'class'        => 'CommonBundle:LegalEntity',
            'placeholder'  => ' - Выберите поручителя - ',
            'choice_label' => 'nameAndShortName',
        ]);
        $fb->add('executor', EntityType::class, [
            'required'     => false,
            'label'        => 'Исполнитель',
            'class'        => 'CommonBundle:LegalEntity',
            'placeholder'  => ' - Выберите исполнителя - ',
            'choice_label' => 'nameAndShortName',
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($request->isMethod('post')) {
            if ($form->get('type')->getData() == 'bilateral') {
                if (!$form->get('executor')->getData()) {
                    $error = new FormError('Исполнитель должен быть выбран');
                    $form->get('executor')->addError($error);
                }
            }

            if ($form->get('type')->getData() == 'tripartite') {
                if (!$form->get('executor')->getData()) {
                    $error = new FormError('Исполнитель должен быть выбран.');
                    $form->get('executor')->addError($error);
                }

                if (!$form->get('guarantor')->getData()) {
                    $error = new FormError('Поручитель должен быть выбран.');
                    $form->get('guarantor')->addError($error);
                } elseif ($form->get('executor')->getData() == $form->get('guarantor')->getData()) {
                    $error = new FormError('Поручитель и исполнитель должны быть разными.');
                    $form->get('guarantor')->addError($error);
                }
            }

            if ($form->isValid()) {
                $agreement->setWorkplace($workplace);
                $agreement->setService($service);
                $agreement->setType($form->get('type')->getData());
                if ($form->get('type')->getData() == 'bilateral') {
                    $agreement->setExecutor($form->get('executor')->getData());
                    $agreement->setGuarantor(null);
                    $this->em->persist($agreement);
                } elseif ($form->get('type')->getData() == 'tripartite') {
                    $agreement->setExecutor($form->get('executor')->getData());
                    $agreement->setGuarantor($form->get('guarantor')->getData());
                    $this->em->persist($agreement);
                } else {
                    $this->em->remove($agreement);
                }

                $this->em->flush();

                return $this->redirectToRoute('admin_agreements', ['workplace' => $workplace->getId()]);
            }
        }

        return $this->render('AppBundle:Admin/Agreement:edit_type.html.twig', [
            'agreement' => $agreement,
            'workplace' => $workplace,
            'service'   => $service,
            'form'      => $form->createView(),
        ]);
    }

    /** @Route("/agreement-print-{workplaceId}-{serviceId}-{subject}/", name="admin_agreement_print") */
    public function agreementPrintAction($workplaceId, $serviceId, $subject)
    {
        /** @var $agreement \KreaLab\CommonBundle\Entity\Agreement */
        $agreement = $this->em->getRepository('CommonBundle:Agreement')->createQueryBuilder('a')
            ->leftJoin('a.service', 's')->addSelect('s')
            ->andWhere('a.workplace = :workplace')->setParameter('workplace', $workplaceId)
            ->andWhere('a.service = :service')->setParameter('service', $serviceId)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        $service = $this->em->getRepository('CommonBundle:Service')->find($serviceId);
        if (!$service) {
            throw $this->createNotFoundException('No service');
        }

        if (!$agreement) {
            throw $this->createNotFoundException('No agreement');
        }

        /** @var $workplace \KreaLab\CommonBundle\Entity\Workplace */
        $workplace = $this->em->getRepository('CommonBundle:Workplace')->find($workplaceId);
        if (!$workplace) {
            throw $this->createNotFoundException('No workplace');
        }

        $numParts   = [];
        $numParts[] = $workplace->getLegalEntity()->getId();
        $numParts[] = $workplace->getId();
        $numParts[] = $service->getId();
        $numParts[] = time() - strtotime('2016-01-01');

        $params = [
            'num'            => implode('-', $numParts),
            'last_name'      => 'ОченьДлиннаяФамилия',
            'first_name'     => 'ОченьДлинноеИмя',
            'patronymic'     => 'ОченьДлинноеОтчество',
            'birthday'       => new \DateTime('1990-01-01'),
            'd_license_date' => new \DateTime('2000-01-01'),
            'sex'            => 1,
            'phone'          => ' (123) 456-78-90',
            'passport'       => 'Очень длинные и непонятные данные паспорта,'
                .' которые могут быть неверными или не совсем точными',
            'address'        => 'Очень длинный и запутанный адрес неизвестного никому места'
                .' в глубинке нашей необъятной страны',
            'sum'            => '12345',
            'sum_online'     => '12345',
        ];

        return $this->render('AppBundle:Operator/Service:agreement.html.twig', [
            'agreement'             => $agreement,
            'workplace'             => $workplace,
            'subject'               => $subject,
            'params'                => $params,
            'service'               => $service,
            'operatorName'          => 'ОченьДлиннаяФамилияОператора ОченьДлинноеИмяОператора'
                .' ОченьДлинноеОтчествоОператора',
            'operatorPowerAttorney' => 'Оп-1234567890',
        ]);
    }

    /** @Route("/cashier-turnover/", name="admin_cashier_turnover") */
    public function cashierTurnoverAction(Request $request)
    {
        $sumStart = 0;
        $sumEnd   = 0;

        $fb = $this->createFormBuilder([
            'csrf_protection'    => false,
            'translation_domain' => false,
        ]);
        $fb->add('supervisor', EntityType::class, [
            'label'         => 'Супервайзер',
            'placeholder'   => ' - Выберите супервайзера - ',
            'required'      => false,
            'class'         => 'CommonBundle:User',
            'constraints'   => new Assert\NotBlank(),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_SUPERVISOR%')
                    ->addOrderBy('u.last_name')
                    ->addOrderBy('u.first_name')
                    ->addOrderBy('u.patronymic');
            },
        ]);
        $fb->add('start_time', DateType::class, [
            'label'    => 'Дата начала',
            'required' => false,
        ]);
        $fb->add('end_time', DateType::class, [
            'label'    => 'Дата окончания',
            'required' => false,

        ]);

        $filterForm = $fb->getForm();
        $filterForm->handleRequest($request);

        if ($filterForm->isValid()) {
            $qb = $this->em->getRepository('CommonBundle:CashierGettingLog')->createQueryBuilder('cgl');

            $data = null;

            $data = $filterForm->get('start_time')->getData();
            if ($data) {
                $qb->andWhere('cgl.created_at >= :start_time')->setParameter('start_time', $data);
            }

            $data = $filterForm->get('end_time')->getData();
            if ($data) {
                $qb->andWhere('cgl.created_at <= :end_time')->setParameter('end_time', $data);
            }

            $data = $filterForm->get('supervisor')->getData();
            if ($data) {
                $qb->andWhere('cgl.supervisor = :supervisor')->setParameter('supervisor', $data);
            }

            $startTime = 0;
            if ($filterForm->get('start_time')->getData()) {
                $startTime = $filterForm->get('start_time')->getData();
            }

            $sumStart = (int)$this->em->getRepository('CommonBundle:CashierGettingLog')
                ->createQueryBuilder('cgl')
                ->select('SUM(cgl.sum)')
                ->andWhere('cgl.created_at <= :start_time')
                ->setParameter('start_time', $startTime)
                ->getQuery()->getSingleScalarResult()
            ;

            $endTime = new \DateTime();
            if ($filterForm->get('end_time')->getData()) {
                $endTime = $filterForm->get('end_time')->getData();
            }

            $sumEnd = (int)$this->em->getRepository('CommonBundle:CashierGettingLog')
                ->createQueryBuilder('cgl')
                ->select('SUM(cgl.sum)')
                ->andWhere('cgl.created_at <= :end_time')
                ->setParameter('end_time', $endTime)
                ->getQuery()->getSingleScalarResult()
            ;

            $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
            $pagerfanta->setMaxPerPage(100);
            $pagerfanta->setCurrentPage($request->get('page', 1));
        }

        return $this->render('AppBundle:Admin:cashier_turnover.html.twig', [
            'filter_form' => $filterForm->createView(),
            'pagerfanta'  => isset($pagerfanta) ? $pagerfanta : null,
            'sum_start'   => $sumStart,
            'sum_end'     => $sumEnd,
        ]);
    }

    /** @Route("/supervisor-turnover/", name="admin_supervisor_turnover") */
    public function supervisorTurnoverAction(Request $request)
    {
        $sumStart = 0;
        $sumEnd   = 0;

        $fb = $this->createFormBuilder([
            'csrf_protection'    => false,
            'translation_domain' => false,
        ]);
        $fb->add('cashier', EntityType::class, [
            'label'         => 'Кассир',
            'placeholder'   => ' - Выберите кассира - ',
            'required'      => false,
            'class'         => 'CommonBundle:User',
            'constraints'   => new Assert\NotBlank(),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_CASHIER%')
                    ->addOrderBy('u.last_name')
                    ->addOrderBy('u.first_name')
                    ->addOrderBy('u.patronymic');
            },
        ]);
        $fb->add('start_time', DateType::class, [
            'label'    => 'Дата начала',
            'required' => false,
        ]);
        $fb->add('end_time', DateType::class, [
            'label'    => 'Дата окончания',
            'required' => false,
        ]);

        $filterForm = $fb->getForm();
        $filterForm->handleRequest($request);

        if ($filterForm->isValid()) {
            $qb = $this->em->getRepository('CommonBundle:CashierGettingLog')->createQueryBuilder('cgl');

            $data = null;

            $data = $filterForm->get('start_time')->getData();
            if ($data) {
                $qb->andWhere('cgl.created_at >= :start_time')->setParameter('start_time', $data);
            }

            $data = $filterForm->get('end_time')->getData();
            if ($data) {
                $qb->andWhere('cgl.created_at <= :end_time')->setParameter('end_time', $data);
            }

            $data = $filterForm->get('cashier')->getData();
            if ($data) {
                $qb->andWhere('cgl.cashier = :cashier')->setParameter('cashier', $data);
            }

            $startTime = 0;
            if ($filterForm->get('start_time')->getData()) {
                $startTime = $filterForm->get('start_time')->getData();
            }

            $sumStart = (int)$this->em->getRepository('CommonBundle:CashierGettingLog')
                ->createQueryBuilder('cgl')
                ->select('SUM(cgl.sum)')
                ->andWhere('cgl.created_at <= :start_time')
                ->setParameter('start_time', $startTime)
                ->getQuery()->getSingleScalarResult()
            ;

            $endTime = new \DateTime();
            if ($filterForm->get('end_time')->getData()) {
                $endTime = $filterForm->get('end_time')->getData();
            }

            $sumEnd = (int)$this->em->getRepository('CommonBundle:CashierGettingLog')
                ->createQueryBuilder('cgl')
                ->select('SUM(cgl.sum)')
                ->andWhere('cgl.created_at <= :end_time')
                ->setParameter('end_time', $endTime)
                ->getQuery()->getSingleScalarResult()
            ;

            $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
            $pagerfanta->setMaxPerPage(100);
            $pagerfanta->setCurrentPage($request->get('page', 1));
        }

        return $this->render('AppBundle:Admin:supervisor_turnover.html.twig', [
            'filter_form' => $filterForm->createView(),
            'pagerfanta'  => isset($pagerfanta) ? $pagerfanta : null,
            'sum_start'   => $sumStart,
            'sum_end'     => $sumEnd,
        ]);
    }
}
