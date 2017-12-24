<?php

namespace KreaLab\AppBundle\Controller;

use Doctrine\ORM\EntityRepository;
use KreaLab\AdminSkeletonBundle\Form\Type\Measure;
use KreaLab\CommonBundle\Entity\ActionLog;
use KreaLab\CommonBundle\Entity\BlankLifeLog;
use KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope;
use KreaLab\CommonBundle\Entity\Envelope;
use KreaLab\CommonBundle\Entity\Image;
use KreaLab\CommonBundle\Entity\Service;
use KreaLab\CommonBundle\Entity\ServiceLog;
use KreaLab\CommonBundle\Entity\ShiftLog;
use KreaLab\CommonBundle\Entity\User;
use KreaLab\CommonBundle\Exception\CommonResponseException;
use KreaLab\CommonBundle\Util\BlankIntervals;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/** @Route("/operator-blanks") */
class OperatorController extends Controller
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var \KreaLab\CommonBundle\Entity\Workplace */
    protected $workplace;

    public function getWorkplace()
    {
        return $this->workplace;
    }

    public function getUser()
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        $token = $this->container->get('security.token_storage')->getToken();

        if ($token === null) {
            return null;
        }

        $user = $token->getUser(); /** @var $user \KreaLab\CommonBundle\Entity\User */

        if (!is_object($user)) {
            // e.g. anonymous authentication
            return null;
        }

        if ($user->getSuccessor()) {
            if ($user->isOperator()) {
                $request = $this->get('request_stack')->getCurrentRequest();

                $log = new ActionLog();
                $log->setUser($user);
                $log->setActionType('logout');
                $log->setParams([
                    'ip'     => $request->getClientIp(),
                    'reason' => 'successor_is_exists',
                ]);
                $this->em->persist($log);
                $this->em->flush();

                $this->get('security.token_storage')->setToken(null);
                $request->getSession()->invalidate();

                $this->addFlash('danger', 'Вас заменили');
                throw new CommonResponseException($this->redirectToRoute('login'));
            } else {
                $this->addFlash('danger', 'Вас заменили');
                throw new CommonResponseException($this->redirectToRoute('homepage'));
            }
        }

        if ($user->getPredecessor()) {
            return $user->getPredecessor();
        }

        return $user;
    }

    public function getUserOrSuccessor()
    {
        $user = $this->getUser(); /** @var $user \KreaLab\CommonBundle\Entity\User */

        if ($user->getSuccessor()) {
            return $user->getSuccessor();
        }

        return $user;
    }

    public function init()
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
        $user     = $this->getUser();
        $request  = $this->get('request_stack')->getCurrentRequest();

        if ($user instanceof User && $user->hasRole('ROLE_OPERATOR')) {
            $session    = $this->get('session');
            $sWorkplace = $session->get('workplace');
            if ($sWorkplace) {
                $cWorkplace = $this->em->find('CommonBundle:Workplace', $sWorkplace);
                if (!$cWorkplace || !$cWorkplace->isActiveAll()) {
                    $session->remove('workplace');

                    return $this->redirectToRoute('homepage');
                }

                $filial = $cWorkplace->getFilial();

                if (!$filial) {
                    return $this->render('AppBundle:Operator/Errors:no_filial.html.twig');
                }

                $filialSchedule = $this->em->getRepository('CommonBundle:Schedule')
                    ->createQueryBuilder('s')
                    ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
                    ->andWhere('s.date = :date')->setParameter('date', new \DateTime('today'))
                    ->andWhere('s.startTime <= :now AND :now <= s.endTime')->setParameter('now', new \DateTime())
                    ->setMaxResults(1)
                    ->getQuery()->getOneOrNullResult();

                if (!$filialSchedule) {
                    $log = new ActionLog();
                    $log->setUser($user);
                    $log->setActionType('login_attempt');
                    $log->setParams([
                        'ip'     => $request->getClientIp(),
                        'reason' => 'no_filial_schedule',
                    ]);
                    $this->em->persist($log);
                    $this->em->flush();

                    if (!$filial) {
                        return $this->render('AppBundle:Operator/Errors:invalid_filial_schedule.html.twig');
                    }
                }

                $operatorSchedule = $this->em->getRepository('CommonBundle:OperatorSchedule')
                    ->createQueryBuilder('os')
                    ->andWhere('os.operator = :operator')->setParameter('operator', $user)
                    ->andWhere('os.date = :date')->setParameter('date', new \DateTime('today'))
                    ->setMaxResults(1)
                    ->getQuery()->getOneOrNullResult();

                if (!$operatorSchedule) {
                    $log = new ActionLog();
                    $log->setUser($user);
                    $log->setActionType('login_attempt');
                    $log->setParams([
                        'ip'     => $request->getClientIp(),
                        'reason' => 'no_operator_schedule',
                    ]);
                    $this->em->persist($log);
                    $this->em->flush();

                    if (!$operatorSchedule) {
                        return $this->render('AppBundle:Operator/Errors:invalid_user_schedule.html.twig');
                    }
                }

                $shiftLog = new ShiftLog();
                $shiftLog->setUser($user);
                $shiftLog->setFilial($filial);
                $shiftLog->setDate(new \DateTime());
                $shiftLog->setStartTime(new \DateTime());
                $this->em->persist($shiftLog);
                $this->em->flush();

                $twig = $this->get('twig');
                $twig->addGlobal('workplace', $cWorkplace);
                $this->workplace = $cWorkplace;

                return null;
            } else {
                if ($user->isOperator()) {
                    $workplaces = $user->getWorkplacesByIp($request->getClientIp());
                } else {
                    $workplaces = [];
                    if ($user->hasOneOfRoles([
                        'ROLE_COURIER',
                        'ROLE_SENIOR_OPERATOR',
                    ])
                    ) {
                        $filials = $user->getFilials();
                    } else {
                        $filials = $this->em->getRepository('CommonBundle:Filial')->findBy(['active' => true]);
                    }

                    foreach ($filials as $filial) {
                        /** @var $filial \KreaLab\CommonBundle\Entity\Filial */
                        if (in_array($request->getClientIp(), $filial->getIps())) {
                            $workplaces = array_merge($workplaces, $filial->getActiveWorkplaces());
                        }
                    }
                }

                if (!$workplaces) {
                    if ($user->isOperator()) {
                        $log = new ActionLog();
                        $log->setUser($user);
                        $log->setActionType('logout');
                        $log->setParams([
                            'ip'     => $request->getClientIp(),
                            'reason' => 'workplace_not_found',
                        ]);
                        $this->em->persist($log);
                        $this->em->flush();

                        $this->get('security.token_storage')->setToken(null);
                        $request->getSession()->invalidate();

                        $this->addFlash('danger', 'У вас нет рабочего места.');
                        return $this->redirectToRoute('login');
                    }

                    $this->addFlash('danger', 'У вас нет рабочего места.');
                    return $this->redirectToRoute('homepage');
                }

                if (count($workplaces) == 1) {
                    /** @var $workplace \KreaLab\CommonBundle\Entity\Workplace */
                    $workplace = $workplaces[0];
                    $wid       = $workplace->getId();
                    $session->set('workplace', $wid);
                    if ($this->getUserOrSuccessor()->isOperator()) {
                        $this->em->getRepository('CommonBundle:ActionLog')
                            ->addParams($this->getUserOrSuccessor(), ['workplace' => $wid]);
                    }

                    return $this->redirect($request->getUri());
                }

                $choices = [];
                foreach ($workplaces as $workplace) { /** @var $workplace \KreaLab\CommonBundle\Entity\Workplace */
                    if (!isset($choices[$workplace->getFilial()->getName()])) {
                        $choices[$workplace->getFilial()->getName()] = [];
                    }

                    $choices[$workplace->getFilial()->getName()][$workplace->getName()] = $workplace->getId();
                }

                if (count($choices) == 1) {
                    $choices = array_values($choices)[0];
                }

                $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
                $fb->add('workplace', ChoiceType::class, [
                    'label'             => 'Рабочее место',
                    'choices'           => $choices,
                    'attr'              => ['bsize' => 4],
                    'choices_as_values' => true,
                ]);
                $fb->add('save', SubmitType::class, ['label' => 'Выбрать']);
                $form = $fb->getForm();

                $form->handleRequest($request);
                if ($form->isValid()) {
                    $wid = $form->get('workplace')->getData();
                    $session->set('workplace', $wid);
                    if ($this->getUserOrSuccessor()->isOperator()) {
                        $this->em->getRepository('CommonBundle:ActionLog')
                            ->addParams($this->getUserOrSuccessor(), ['workplace' => $wid]);
                    }

                    return $this->redirect($request->getUri());
                }

                return $this->render('AppBundle:Operator:choice_workplace.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        } else {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/close-shift/", name="operator__close_shift")
     */
    public function closeShiftAction()
    {
        $shiftLog = $this->em->getRepository('CommonBundle:ShiftLog')->findOneBy([
            'date'    => new \DateTime('today'),
            'user'    => $this->getUser(),
            'endTime' => null,
        ]);

        if (!$shiftLog) {
            throw $this->createNotFoundException('no shift log');
        }

        $shiftLog->setEndTime(new \DateTime());
        $shiftLog->setClosed(true);
        $this->em->persist($shiftLog);
        $this->em->flush();

        $this->addFlash('success', 'Завершили смену');
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/services/duplicates/", name="duplicates")
     */
    public function duplicatesAction(Request $request)
    {
        $sName   = 'duplicates_search';
        $session = $this->get('session');
        $session->remove($sName);

        $fb = $this->container->get('form.factory')->createNamedBuilder(null, FormType::class, null, [
            'csrf_protection'    => false,
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('last_name', TextType::class, [
            'label'       => 'Фамилия',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('first_name', TextType::class, [
            'label'       => 'Имя',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('patronymic', TextType::class, [
            'label'       => 'Отчество',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('birthday', BirthdayType::class, [
            'label' => 'Дата рождения',
            'years' => range(1930, date('Y')),
        ]);
        $fb->setMethod('get');
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $serviceLogs = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
                ->groupBy('sl')
                ->leftJoin('sl.service', 's')->addSelect('s')
                ->andWhere('s.is_not_duplicate_price = :is_not_duplicate_price')
                ->setParameter('is_not_duplicate_price', false)
                ->leftJoin('sl.blank', 'b')->addSelect('b')
                ->andWhere('sl.date_giving >= DATE_SUB(CURRENT_DATE(), s.lifetime, \'MONTH\')')
                ->andWhere('s.duplicate_price > 0')
                ->andWhere('sl.last_name = :last_name')
                ->setParameter('last_name', $form->get('last_name')->getData())
                ->andWhere('sl.first_name = :first_name')
                ->setParameter('first_name', $form->get('first_name')->getData())
                ->andWhere('sl.patronymic = :patronymic')
                ->setParameter('patronymic', $form->get('patronymic')->getData())
                ->andWhere('sl.birthday = :birthday')
                ->setParameter('birthday', $form->get('birthday')->getData())
                ->andWhere('(s.is_gnoch = :gnoch1 AND b.id is NOT NULL ) OR (s.is_gnoch = :gnoch2)')
                ->setParameter('gnoch1', true)->setParameter('gnoch2', false)
                ->leftJoin('sl.medical_center_corrects', 'mcc')->addSelect('mcc')
                ->andWhere('mcc.id IS NULL')
                ->leftJoin('sl.children', 'ch')->addSelect('ch')
                ->andWhere('ch.id is NULL')
                ->getQuery()->execute();

            $agreements = [];
            foreach ($serviceLogs as $serviceLog) { /** @var $serviceLog \KreaLab\CommonBundle\Entity\ServiceLog */
                $agreements[$serviceLog->getId()]
                    = $serviceLog->getService()->getAgreementByWorkplace($this->workplace);
            }

            return $this->render('AppBundle:Operator:Service/duplicates.html.twig', [
                'serviceLogs' => $serviceLogs,
                'agreements'  => $agreements,
            ]);
        }

        return $this->render('AppBundle:Operator:Service/duplicatesearch.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/services/duplicate/{id}/", name="service_duplicate_init")
     */
    public function serviceDuplicateInitAction($id)
    {
        /** @var $serviceLog \KreaLab\CommonBundle\Entity\ServiceLog */
        $serviceLog = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
            ->leftJoin('sl.service', 's')->addSelect('s')
            ->andWhere('s.is_not_duplicate_price = :is_not_duplicate_price')
            ->setParameter('is_not_duplicate_price', false)
            ->andWhere('sl.date_giving >= DATE_SUB(CURRENT_DATE(), s.lifetime, \'MONTH\')')
            ->andWhere('s.duplicate_price > 0')
            ->andWhere('sl.id = :id')->setParameter('id', $id)
            ->leftJoin('sl.medical_center_corrects', 'mcc')->addSelect('mcc')
            ->andWhere('mcc.id IS NULL')
            ->leftJoin('sl.children', 'ch')->addSelect('ch')
            ->andWhere('ch.id is NULL')
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$serviceLog) {
            throw $this->createNotFoundException('No service log');
        }

        $service = $serviceLog->getService();

        $agreement = $service->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException('no agreement');
        }

        $executorOrGuarantor = $agreement->getExecutorOrGuarantor();

        if ($service->getReferenceType()) {
            $isStamp      = true;
            $refType      = $service->getReferenceType();
            $blanksAmount = $this->em->getRepository('CommonBundle:Blank')
                ->getCurrentIntervalsAmount($this->getUserOrSuccessor(), $isStamp, $executorOrGuarantor, $refType);

            if (!$blanksAmount) {
                throw $this->createNotFoundException('Нет бланков');
            }
        }

        $sName   = 'service_'.$service->getId();
        $session = $this->get('session');
        $session->remove($sName);

        $numParts     = [];
        $numParts[]   = $executorOrGuarantor->getId();
        $numParts[]   = $this->workplace->getId();
        $numParts[]   = $id;
        $numParts[]   = time() - strtotime('2016-01-01');
        $sData['num'] = implode('-', $numParts);

        $params = $serviceLog->getParams();
        if (isset($params['sum_online'])) {
            unset($params['sum_online']);
        }

        $sData                   = array_merge($sData, $params);
        $sData['year']           = date('Y', strtotime($params['birthday']->date));
        $sData['month']          = date('m', strtotime($params['birthday']->date));
        $sData['day']            = date('d', strtotime($params['birthday']->date));
        $sData['eeg_conclusion'] = $serviceLog->getEegConclusion();
        $sData['sum_type']       = 'duplicate';
        $sData['sum']            = $serviceLog->getService()->getDuplicatePrice();
        $sData['parent_id']      = $serviceLog->getId();
        $sData['step']           = 1;
        $session->set($sName, $sData);

        return $this->redirectToRoute('service', ['id' => $service->getId()]);
    }

    /**
     * @Route("/services/", name="services")
     * @Template
     */
    public function servicesAction()
    {
        $services = $this->em->getRepository('CommonBundle:Service')->createQueryBuilder('s')
            ->andWhere('s.active = :active')->setParameter('active', true)
            ->addOrderBy('s.position', 'asc')
            ->getQuery()->getResult();

        $servicesInfo = [];
        foreach ($services as $service) { /** @var $service \KreaLab\CommonBundle\Entity\Service */
            $agreement = $service->getAgreementByWorkplace($this->workplace);
            $executor  = $agreement ? $agreement->getExecutor() : null;
            $guarantor = $agreement ? $agreement->getGuarantor() : null;
            $isStamp   = !$service->getIsGnoch();
            $refType   = $service->getReferenceType();

            $executorOrGuarantor = $agreement ? $agreement->getExecutorOrGuarantor() : null;

            $currentBlanks = $this->em->getRepository('CommonBundle:Blank')
                ->getCurrentIntervalsAmount($this->getUserOrSuccessor(), $isStamp, $executorOrGuarantor, $refType);

            $onHandsBlanks = $this->em->getRepository('CommonBundle:Blank')
                ->getOnHandsAmount($this->getUserOrSuccessor(), $isStamp, $executorOrGuarantor, $refType);

            $servicesInfo[$service->getId()] = [
                'is_disabled'            => (!$agreement || !$agreement->getType() || $currentBlanks == 0),
                'executor'               => $executor,
                'guarantor'              => $guarantor,
                'amount_current_blanks'  => $currentBlanks,
                'amount_on_hands_blanks' => $onHandsBlanks,
            ];
        }

        return [
            'services'      => $services,
            'services_info' => $servicesInfo,
        ];
    }

    /**
     * @Route("/services/service/{id}/", name="service_service_init")
     */
    public function serviceServiceInitAction($id)
    {
        $service = $this->em->find('CommonBundle:Service', $id);
        if (!$service) {
            throw $this->createNotFoundException();
        }

        $agreement = $service->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException('no agreement');
        }

        $executorOrGuarantor = $agreement->getExecutorOrGuarantor();

        if ($service->getReferenceType()) {
            $isStamp      = !$service->getIsGnoch();
            $refType      = $service->getReferenceType();
            $blanksAmount = $this->em->getRepository('CommonBundle:Blank')
                ->getCurrentIntervalsAmount($this->getUserOrSuccessor(), $isStamp, $executorOrGuarantor, $refType);

            if (!$blanksAmount) {
                throw $this->createNotFoundException('Нет бланков');
            }
        }

        $session = $this->get('session');
        $session->remove('service_'.$id);
        $sName = 'service_'.$service->getId();

        $numParts   = [];
        $numParts[] = $executorOrGuarantor->getId();
        $numParts[] = $this->workplace->getId();
        $numParts[] = $id;
        $numParts[] = time() - strtotime('2016-01-01');

        $sData['num']       = implode('-', $numParts);
        $sData['parent_id'] = false;
        $sData['step']      = 1;
        $session->set($sName, $sData);

        return $this->redirectToRoute('service', ['id' => $id]);
    }

    /**
     * @Route("/services/{id}/", name="service")
     */
    public function serviceAction(Request $request, $id)
    {
        $service = $this->em->find('CommonBundle:Service', $id);
        if (!$service) {
            throw $this->createNotFoundException();
        }

        $session = $this->get('session');
        $sName   = 'service_'.$service->getId();
        $sData   = $session->get($sName);

        switch ($sData['step']) {
            case 1:
                return $this->step1($request, $service, $sName);
            case 2:
                return $this->step2($request, $service, $sName);
            case 3:
                return $this->step3($request, $service, $sName);
            case 4:
                return $this->step4($request, $service, $sName);
            case 5:
                return $this->step5($request, $service, $sName);
            case 6:
                return $this->step6($request, $service, $sName);
            case 7:
                return $this->step7($request, $service, $sName);
            default:
                throw $this->createNotFoundException();
        }
    }

    /**
     * @Route("/services/{id}/step-{step}/", name="service_step")
     */
    public function serviceStepAction($id, $step)
    {
        $service = $this->em->find('CommonBundle:Service', $id);
        if (!$service) {
            throw $this->createNotFoundException();
        }

        $session = $this->get('session');
        $sName   = 'service_'.$service->getId();
        if (!$session->has($sName)) {
            return $this->redirectToRoute('service', ['id' => $service->getId()]);
        }

        $sData = $session->get($sName);
        if (!isset($sData['step_max']) || $sData['step_max'] < $sData['step']) {
            $sData['step_max'] = $sData['step'];
        }

        $sData['step'] = min($sData['step_max'], $step);
        $session->set($sName, $sData);

        return $this->redirectToRoute('service', ['id' => $service->getId()]);
    }

    /**
     * @Route("/services/{id}/print-agreement/", name="service_print_agreement")
     */
    public function servicePrintAgreementAction($id)
    {
        $service = $this->em->find('CommonBundle:Service', $id);
        if (!$service) {
            throw $this->createNotFoundException();
        }

        /** @var $agreement \KreaLab\CommonBundle\Entity\Agreement */
        $agreement = $this->em->getRepository('CommonBundle:Agreement')->createQueryBuilder('a')
            ->andWhere('a.service = :service')->setParameter('service', $service)
            ->andWhere('a.workplace = :workplaceId')->setParameter('workplaceId', $this->workplace)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if (!$agreement) {
            throw $this->createNotFoundException('no agreement');
        }

        $session = $this->get('session');
        $sName   = 'service_'.$service->getId();
        $sData   = $session->get($sName);

        if (!$session->has($sName)
            || ($sData['step'] < 3 && (!isset($sData['step_max']) || $sData['step_max'] < 3))
        ) {
            throw $this->createNotFoundException();
        }

        if ($this->has('profiler')) {
            $this->get('profiler')->disable();
        }

        /** @var  $user \KreaLab\CommonBundle\Entity\User */
        $user         = $this->getUser();
        $operatorName = $user->getLastName().' '.$user->getFirstName().' '.$user->getPatronymic();

        $subject = 'subject';

        if ($sData['parent_id']) {
            $subject = 'duplicate';
        } elseif ($sData['medical_center_error_id']) {
            $subject = 'medical_center_error';
        }

        return $this->render('AppBundle:Operator:Service/agreement.html.twig', [
            'service'               => $service,
            'params'                => $sData,
            'operatorName'          => $operatorName,
            'operatorPowerAttorney' => $user->getPowerAttorney(),
            'agreement'             => $agreement,
            'subject'               => $subject,
        ]);
    }

    /**
     * @Route("/services/{id}/upload-docs/", name="service_upload_docs")
     */
    public function serviceUploadDocsAction(Request $request, $id)
    {
        $result = ['files' => []];
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $service = $this->em->find('CommonBundle:Service', $id);
        if (!$service) {
            throw $this->createNotFoundException();
        }

        $session = $this->get('session');
        $sName   = 'service_'.$service->getId();
        $sData   = $session->get($sName);

        if (!$session->has($sName)
            || ($sData['step'] < 4 && (!isset($sData['step_max']) || $sData['step_max'] < 4))
        ) {
            throw $this->createNotFoundException();
        }

        $sData['docs'] = isset($sData['docs']) ? $sData['docs'] : [];
        $licm          = $this->container->get('liip_imagine.cache.manager');
        foreach ($request->files as $files) {
            foreach ($files as $file) {
                /** @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
                if ($file->getClientSize() > 5 * 1024 * 1024) {
                    $result = ['errors' => ['Слишком большой размер файла.']];
                } elseif (!in_array($file->getMimeType(), ['image/jpg', 'image/jpeg', 'image/png'])) {
                    $result = ['errors' => ['Неразрешённый формат файла.']];
                } else {
                    $doc = new Image();
                    $doc->setUploadFile($file);
                    $this->em->persist($doc);
                    $this->em->flush();

                    $sData['docs'][] = $doc->getId();
                    $session->set($sName, $sData);

                    $result['files'][] = [
                        'id'          => $doc->getId(),
                        'webPath'     => $licm->getBrowserPath($doc->getWebPath(), 'doc'),
                        'webPathOrig' => $doc->getWebPath(),
                    ];
                }
            }
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/services/{id}/delete-docs/", name="service_delete_docs")
     */
    public function serviceDeleteDocsAction(Request $request, $id)
    {
        $result = [];

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $service = $this->em->find('CommonBundle:Service', $id);
        if (!$service) {
            throw $this->createNotFoundException();
        }

        $session = $this->get('session');
        $sName   = 'service_'.$service->getId();
        $sData   = $session->get($sName);

        if (!$session->has($sName)
            || ($sData['step'] < 4 && (!isset($sData['step_max']) || $sData['step_max'] < 4))
        ) {
            throw $this->createNotFoundException();
        }

        $sData['docs'] = isset($sData['docs']) ? $sData['docs'] : [];

        if ($request->isMethod('post')) {
            $docId = $request->get('doc_id');
            $key   = array_search($docId, $sData['docs']);
            if ($key !== false) {
                unset($sData['docs'][$key]);
                $session->set($sName, $sData);
            }
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/current/", name="current")
     * @Template
     */
    public function currentAction()
    {
        $sumLogs   = 0;
        $sumOrders = 0;

        $lLogs    = [];
        $lastLogs = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
            ->andWhere('DATE(sl.created_at) < :today')->setParameter('today', new \DateTime('today'))
            ->andWhere('sl.workplace = :workplace')->setParameter('workplace', $this->workplace)
            ->andWhere('sl.envelope IS NULL')
            ->andWhere('sl.import = 0')
            ->getQuery()->getArrayResult()
        ;
        foreach ($lastLogs as $log) {
            $date = date_format($log['created_at'], 'Y-m-d');
            if (!isset($lLogs[$date])) {
                $lLogs[$date] = [
                    'all'    => 0,
                    'logs'   => 0,
                    'orders' => 0,
                ];
            }

            $lLogs[$date]['all']  = $lLogs[$date]['all'] + $log['sum'];
            $lLogs[$date]['logs'] = $lLogs[$date]['logs'] + $log['sum'];
            $sumLogs              = $sumLogs + $log['sum'];
        }

        $lastOrders = $this->em->getRepository('CommonBundle:Order')->createQueryBuilder('o')
            ->andWhere('DATE(o.updated_at) < :today')->setParameter('today', new \DateTime('today'))
            ->andWhere('o.workplace = :workplace')->setParameter('workplace', $this->workplace)
            ->andWhere('o.envelope is null')
            ->getQuery()->getArrayResult()
        ;
        foreach ($lastOrders as $order) {
            $date = date_format($order['updated_at'], 'Y-m-d');
            if (!isset($lLogs[$date])) {
                $lLogs[$date] = [
                    'all'    => 0,
                    'logs'   => 0,
                    'orders' => 0,
                ];
            }

            $lLogs[$date]['all']    = $lLogs[$date]['all'] - $order['sum'];
            $lLogs[$date]['orders'] = $lLogs[$date]['orders'] - $order['sum'];
            $sumOrders              = $sumOrders - $order['sum'];
        }

        $logs  = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
            ->leftJoin('sl.service', 's')->addSelect('s')
            ->andWhere('DATE(sl.created_at) = :today')->setParameter('today', new \DateTime('today'))
            ->andWhere('sl.workplace = :workplace')->setParameter('workplace', $this->workplace)
            ->andWhere('sl.envelope IS NULL')
            ->andWhere('sl.import = 0')
            ->addOrderBy('sl.created_at')
            ->getQuery()->execute()
        ;
        $items = [];
        foreach ($logs as $log) { /** @var $log \KreaLab\CommonBundle\Entity\ServiceLog */
            $numBlank = '';
            $serie    = '';
            if ($log->getBlank()) {
                $numBlank = $log->getBlank()->getNumber();
                $serie    = $log->getBlank()->getSerie();
            }

            $item               = [];
            $item['id']         = $log->getId();
            $item['created_at'] = $log->getCreatedAt()->format('Y-m-d H:i:s');
            $item['service']    = (count($log->getMedicalCenterCorrects()) ? 'Х/' : '')
                .($log->getMedicalCenterError() ? 'О/' : '')
                .($log->getParent() ? 'Д/' : '').$log->getService()->getCode();
            $item['num_blank']  = $numBlank;
            $item['client']     = $log->getLastName().' '.$log->getFirstName().' '.$log->getPatronymic();
            $item['operator']   = $log->getOperator();
            $item['serie']      = $serie;
            $item['num_check']  = $log->getParams()['num_check'];
            $item['discount']   = $log->getParams()['discount'];
            $item['sum']        = $log->getSum();
            $item['cashbox']    = $log->getCashbox();
            $items[]            = $item;
            $sumLogs            = $sumLogs + $log->getSum();
        }

        $orders = $this->em->getRepository('CommonBundle:Order')->createQueryBuilder('o')
            ->andWhere('DATE(o.updated_at) = :today')->setParameter('today', new \DateTime('today'))
            ->andWhere('o.workplace = :workplace')->setParameter('workplace', $this->workplace)
            ->andWhere('o.envelope is null')
            ->getQuery()->execute()
        ;
        foreach ($orders as $order) { /** @var $order \KreaLab\CommonBundle\Entity\Order */
            $item                   = [];
            $item['id']             = $order->getId();
            $item['created_at']     = $order->getUpdatedAt()->format('Y-m-d H:i:s');
            $item['service']        = 'Ордер';
            $item['acquittanceman'] = $order->getAcquittanceman();
            $item['operator']       = $order->getOperator();
            $item['appointment']    = $order->getAppointment();
            $item['sum']            = -$order->getSum();
            $item['is_order']       = true;
            $items[]                = $item;
            $sumOrders              = $sumOrders - $order->getSum();
        }

        usort($items, function ($first, $second) {
            return ($first['created_at'] < $second['created_at']) ? -1 : 1;
        });

        return [
            'orders'    => $orders,
            'logs'      => $logs,
            'lLogs'     => $lLogs,
            'sumLogs'   => $sumLogs,
            'sumOrders' => $sumOrders,
            'items'     => $items,
        ];
    }

    /**
     * @Route("/create-envelope/", name="create_envelope")
     */
    public function createEnvelopeAction()
    {
        $sum      = 0;
        $envelope = new Envelope();
        $envelope->setOperator($this->getUser());
        $envelope->setWorkplace($this->workplace);

        $logs = $this->em->getRepository('CommonBundle:ServiceLog')->findBy([
            'workplace' => $this->workplace,
            'envelope'  => null,
            'import'    => false,
        ]);
        foreach ($logs as $log) { /** @var $log \KreaLab\CommonBundle\Entity\ServiceLog */
            $log->setEnvelope($envelope);
            $this->em->persist($log);
            $sum += $log->getSum();
        }

        $orders = $this->em->getRepository('CommonBundle:Order')->findBy([
            'workplace' => $this->workplace,
            'envelope'  => null,
        ]);
        foreach ($orders as $order) { /** @var $order \KreaLab\CommonBundle\Entity\Order */
            $order->setEnvelope($envelope);
            $this->em->persist($order);
            $sum -= $order->getSum();
        }

        if ($sum > 0) {
            $envelope->setSum($sum);
            $this->em->persist($envelope);
            $this->workplace->setSum($this->workplace->getSum() - $sum);
            $this->em->persist($this->workplace);
            $this->em->flush();
            $this->addFlash('success', 'Сформирован конверт №'.$envelope->getId().' на сумму '.$sum.' руб.');
        } else {
            $this->addFlash('danger', 'Нет данных для формирования конверта.');
        }

        return $this->redirectToRoute('current');
    }

    protected function step1(Request $request, Service $service, $sName)
    {
        $agreement = $service->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException();
        }

        $session = $this->get('session');
        $sData   = $session->get($sName);

        if (!empty($sData['medical_center_error_id'])) {
            $sData = array_merge($sData, [
                'sum'       => 0,
                'revisit'   => false,
                'sum_type'  => 'medical_center_error',
                'parent_id' => null,
            ]);
            $session->set($sName, array_merge($session->get($sName), $sData));
        }

        $fb = $this->createFormBuilder(null, [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('last_name', TextType::class, [
            'label'       => 'Фамилия',
            'constraints' => new Assert\NotBlank(),
            'disabled'    => $sData['parent_id'],
        ]);
        $fb->add('first_name', TextType::class, [
            'label'       => 'Имя',
            'constraints' => new Assert\NotBlank(),
            'disabled'    => $sData['parent_id'],
        ]);
        $fb->add('patronymic', TextType::class, [
            'label'       => 'Отчество',
            'constraints' => new Assert\NotBlank(),
            'disabled'    => $sData['parent_id'],
        ]);
        $fb->add('birthday', BirthdayType::class, [
            'label'    => 'Дата рождения',
            'years'    => range(1930, date('Y')),
            'disabled' => $sData['parent_id'],
        ]);
        $fb->add('save', SubmitType::class, [
            'label' => 'Продолжить',
            'attr'  => ['class' => 'btn-success btn-lg'],
        ]);
        $form = $fb->getForm();
        $form->setData($sData);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $findForm = [
                'last_name'  => $form->get('last_name')->getData(),
                'first_name' => $form->get('first_name')->getData(),
                'patronymic' => $form->get('patronymic')->getData(),
                'birthday'   => $form->get('birthday')->getData(),
            ];

            if (!empty($sData['medical_center_error_id'])) {
                $sData = array_merge($sData, $findForm);
            } elseif (empty($sData['parent_id'])) {
                $findNow = [
                    'phone'                   => null,
                    'passport'                => null,
                    'discount_comment'        => null,
                    'd_license_data'          => null,
                    'year'                    => null,
                    'month'                   => null,
                    'day'                     => null,
                    'sex'                     => 1,
                    'address'                 => null,
                    'sum_type'                => null,
                    'sum'                     => $service->getPrice(),
                    'eeg_conclusion'          => '',
                    'revisit'                 => false,
                    'category_id_list'        => null,
                    'medical_center_error_id' => null,
                    'man_id'                  => null,
                    'man_full_name'           => null,
                    'man_full_name_genitive'  => null,
                    'eeg_id'                  => null,
                    'legal_entity_id'         => null,
                ];

                $sData             = array_merge($findNow, $findForm);
                $visitedServiceLog = $this->em->getRepository('CommonBundle:ServiceLog')
                    ->findOneBy($findForm, ['created_at' => 'DESC']);
                if ($visitedServiceLog) {
                    $visitedParams = $visitedServiceLog->getParams();

                    if ($service->getIsRevisitPrice()) {
                        $visitedParams['sum']      = $service->getRevisitPrice();
                        $visitedParams['revisit']  = 'revisit';
                        $visitedParams['sum_type'] = 'revisit';
                        $visitedParams['docs']     = null;
                    };
                    $visitedParams['parent_id']               = 0;
                    $visitedParams['medical_center_error_id'] = false;
                    $sData                                    = array_merge($sData, $visitedParams);
                }
            }

            $sData['step'] = 2;

            $session->set($sName, array_merge($session->get($sName), $sData));

            return $this->redirectToRoute('service', ['id' => $service->getId()]);
        }

        return $this->render('AppBundle:Operator:Service/step1.html.twig', [
            'service'   => $service,
            'form'      => $form->createView(),
            's_data'    => $sData,
            'agreement' => $agreement,
        ]);
    }

    protected function step2(Request $request, Service $service, $sName)
    {
        $agreement = $service->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException();
        }

        $session = $this->get('session');
        $sData   = $session->get($sName);

        if (!empty($sData['medical_center_error_id'])) {
            $sData['step'] = 7;
            $session->set($sName, $sData);
            return $this->redirectToRoute('service', ['id' => $service->getId()]);
        }

        $categories = [];
        if (isset($sData['category_id_list'])) {
            foreach ($sData['category_id_list'] as $id) {
                $categories[$id] = $this->em->find('CommonBundle:Category', $id);
            };
        }

        $init = [
            'category' => $categories,
            'sex'      => $sData['sex'],
            'sum_type' => $sData['sum_type'],
            'passport' => $sData['passport'],
            'address'  => $sData['address'],
        ];

        if (!empty($sData['d_license_date'])) {
            $init['d_license_date'] = $sData['d_license_date'];
        }

        $fb = $this->createFormBuilder($init, [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        if ($service->getReferenceType() and $service->getReferenceType()->getDriverReference()) {
            $fb->add('d_license_date', DateType::class, [
                'required'    => false,
                'label'       => 'Дата выдачи прав',
                'years'       => range(1950, date('Y')),
                'disabled'    => !empty($sData['parent_id']),
                'placeholder' => [
                    'year'  => '-',
                    'month' => '-',
                    'day'   => '-',
                ],
            ]);
            $fb->add('category', EntityType::class, [
                'constraints'   => new Assert\Count(['min' => 1]),
                'expanded'      => true,
                'multiple'      => true,
                'label'         => 'Категория прав',
                'disabled'      => !empty($sData['parent_id']),
                'class'         => 'CommonBundle:Category',
                'placeholder'   => ' - выберите категорию прав - ',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')->addOrderBy('e.name');
                },
            ]);
        }

        $fb->add('sex', ChoiceType::class, [
            'label'             => 'Пол',
            'disabled'          => !empty($sData['parent_id']),
            'expanded'          => true,
            'choices_as_values' => true,
            'attr'              => ['class' => 'form-inline'],
            'choices'           => ['Мужской' => 1, 'Женский' => 0],
        ]);
        $fb->add('phone', TextType::class, [
            'label'    => 'Телефон',
            'attr'     => ['bsize' => 3, 'value' => $sData['phone']],
            'disabled' => !empty($sData['parent_id']),
        ]);
        $fb->add('passport', TextType::class, [
            'label'       => 'Паспортные данные',
            'constraints' => new Assert\NotBlank(),
            'disabled'    => !empty($sData['parent_id']),
        ]);
        $fb->add('address', TextType::class, [
            'label'       => 'Адрес',
            'constraints' => new Assert\NotBlank(),
            'disabled'    => !empty($sData['parent_id']),
        ]);

        $discounts = $service->getServicesDiscounts();
        if (!empty($sData['parent_id'])) {
            /** @var $visitedServiceLog \KreaLab\CommonBundle\Entity\ServiceLog */
            $visitedServiceLog = $this->em->getRepository('CommonBundle:ServiceLog')->find($sData['parent_id']);
            $sData['sum_type'] = 'duplicate';
            $sData['sum']      = $visitedServiceLog->getService()->getDuplicatePrice();
            if ($service->getReferenceType()) {
                if (!empty($visitedServiceLog->getBlank()) && !empty($visitedServiceLog->getBlank()->getNumber())) {
                    $sData['parent_number'] = $visitedServiceLog->getBlank()->getNumber();
                } elseif (!empty($visitedServiceLog->getNumBlank())) {
                    $sData['parent_number'] = $visitedServiceLog->getNumBlank();
                }

                if (!empty($visitedServiceLog->getBlank()) && !empty($visitedServiceLog->getBlank()->getSerie())) {
                    $sData['parent_serie'] = $visitedServiceLog->getBlank()->getSerie();
                }
            }
        } elseif (!empty($sData['medical_center_error_id'])) {
            $sums = ['0 руб. — Возврат бланка с опечатками' => 'medical_center_error'];
            $fb->add('sum_type', ChoiceType::class, [
                'label'             => 'Стоимость услуги',
                'choices'           => $sums,
                'choices_as_values' => true,
                'disabled'          => !empty($sData['medical_center_error_id']),
            ]);
        } else {
            if (!empty($sData['revisit']) and $service->getIsRevisitPrice()) {
                $sums[$service->getRevisitPrice().' руб. — Повторное посещение'] = 'revisit';
            }

            $sums[$service->getPrice().' руб. — Основная цена'] = 'main';

            $discounts2 = [];
            foreach ($discounts as $discount) { /** @var $discount \KreaLab\CommonBundle\Entity\ServiceDiscount */
                if ($discount->getDiscount()) {
                    $discounts2[$discount->getDiscount()->getPosition()] = $discount;
                }
            }

            ksort($discounts2);

            foreach ($discounts2 as $discount) { /** @var $discount \KreaLab\CommonBundle\Entity\ServiceDiscount */
                if ($discount->getActive()) {
                    $key        = $discount->getSum().' руб. — '.$discount->getDiscount()->getName();
                    $sums[$key] = $discount->getDiscount()->getId();
                }
            }

            $fb->add('sum_type', ChoiceType::class, [
                'label'             => 'Стоимость услуги',
                'choices'           => $sums,
                'choices_as_values' => true,
                'disabled'          => !empty($sData['medical_center_error_id']),
            ]);
            $fb->add('discount_comment', TextareaType::class, [
                'required' => false,
                'label'    => 'Комментарий к скидке',
                'attr'     => [
                    'placeholder' => 'Введите комментарий не менее 5 символов.',
                    'value'       => $sData['discount_comment'],
                ],
            ]);
        }

        $fb->add('save', SubmitType::class, [
            'label' => 'Продолжить',
            'attr'  => ['class' => 'btn-success btn-lg'],
        ]);
        $form = $fb->getForm();
        $form->setData(array_merge(['sum' => $service->getPrice()], $sData));

        $form->handleRequest($request);
        if ($request->isMethod('post')) {
            $phone = $form->get('phone')->getData();
            if (preg_match('#^\+7\s\((\d{3})\)\s(\d{3})\-(\d{2})\-(\d{2})$#misu', $phone, $matches)) {
                $phone = $matches[1].$matches[2].$matches[3].$matches[4];
            } else {
                $form->get('phone')->addError(new FormError('Неверный формат номера.'));
            }

            $discountComment = '';
            $sumType         = empty($sData['parent_id']) ? $form->get('sum_type')->getData() : $sData['sum_type'];
            if (empty($sData['parent_id'])) {
                if ($sumType != 'main' && !$sData['revisit'] && empty($sData['medical_center_error_id'])) {
                    $discountComment = $form->get('discount_comment')->getData();
                    if (mb_strlen($discountComment) < 5) {
                        $error = 'Комментарий к скидке должен быть не менее 5 символов.';
                        $form->get('discount_comment')->addError(new FormError($error));
                    }
                }
            }

            if ($form->isValid()) {
                if ($service->getReferenceType() and $service->getReferenceType()->getDriverReference()) {
                    $categoriesIdList = []; /** @var $cat \KreaLab\CommonBundle\Entity\Category */
                    foreach ($form->get('category')->getData() as $cat) {
                        $categoriesIdList[] = $cat->getId();
                    }

                    $session->set($sName, array_merge($session->get($sName), [
                        'd_license_date'   => $form->get('d_license_date')->getData(),
                        'category_id_list' => $categoriesIdList,
                    ]));
                }

                $sum        = 0;
                $sNameMerge = [];
                if (empty($sData['parent_id'])) {
                    if ($sumType == 'main') {
                        $sum = $service->getPrice();
                    } elseif ($sumType == 'revisit') {
                        $sum = $service->getRevisitPrice();
                    } else {
                        foreach ($discounts as $discount) {
                            /** @var $discount \KreaLab\CommonBundle\Entity\ServiceDiscount */

                            $serviceDiscount = $this->em->getRepository('CommonBundle:ServiceDiscount')
                                ->createQueryBuilder('sd')
                                ->leftJoin('sd.discount', 'd')->addSelect('d')
                                ->andWhere('sd.active = :active')->setParameter('active', true)
                                ->andWhere('sd.discount = :discount')->setParameter('discount', $sumType)
                                ->andWhere('sd.service = :service')
                                ->setParameter('service', $discount->getService())
                                ->getQuery()->getOneOrNullResult();
                            $sum             = $serviceDiscount->getSum();

                            $sNameMerge['sum_online'] = $serviceDiscount->getDiscount()->getIsOnline() ? $sum : '';
                        }
                    }

                    $sNameMerge = array_merge($sNameMerge, [
                        'step'             => 3,
                        'sex'              => $form->get('sex')->getData(),
                        'phone'            => $phone,
                        'passport'         => $form->get('passport')->getData(),
                        'address'          => $form->get('address')->getData(),
                        'sum_type'         => $sumType,
                        'sum'              => $sum,
                        'discount'         => $service->getPrice() - $sum,
                        'discount_comment' => $discountComment,
                        'd_license_date'   => ($service->getReferenceType()
                            && $service->getReferenceType()->getDriverReference())
                            ? $form->get('d_license_date')->getData() : null,
                    ]);
                } else {
                    $visitedServiceLog = $this->em->find('CommonBundle:ServiceLog', $sData['parent_id']);
                    $sNameMerge        = array_merge($sNameMerge, [
                        'step'             => 3,
                        'sum'              => $visitedServiceLog->getService()->getDuplicatePrice(),
                        'sum_type'         => 'duplicate',
                        'discount'         => 0,
                        'discount_comment' => '',
                    ]);
                }

                $session->set($sName, array_merge($session->get($sName), $sNameMerge));

                return $this->redirectToRoute('service', ['id' => $service->getId()]);
            } else {
                $sData['passport'] = $form->get('passport')->getData();
                $sData['address']  = $form->get('address')->getData();
            }
        }

        $session->set($sName, $sData);

        return $this->render('AppBundle:Operator:Service/step2.html.twig', [
            'service'   => $service,
            'form'      => $form->createView(),
            's_data'    => $sData,
            'agreement' => $agreement,
        ]);
    }

    protected function step3(Request $request, Service $service, $sName)
    {
        $agreement = $service->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException();
        }

        $session = $this->get('session');
        $sData   = $session->get($sName);

        if (!empty($sData['medical_center_error_id'])) {
            $sData['step'] = 7;
            $session->set($sName, $sData);
            return $this->redirectToRoute('service', ['id' => $service->getId()]);
        }

        $fb = $this->createFormBuilder(null, [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('save', SubmitType::class, [
            'label' => 'Продолжить',
            'attr'  => ['class' => 'btn-success btn-lg'],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $session->set($sName, array_merge($session->get($sName), ['step' => 4]));
            return $this->redirectToRoute('service', ['id' => $service->getId()]);
        }

        return $this->render('AppBundle:Operator:Service/step3.html.twig', [
            'service'   => $service,
            'form'      => $form->createView(),
            's_data'    => $sData,
            'agreement' => $agreement,
        ]);
    }

    protected function step4(Request $request, Service $service, $sName)
    {
        $agreement = $service->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException();
        }

        $session = $this->get('session');
        $sData   = $session->get($sName);

        if (!empty($sData['medical_center_error_id'])) {
            $sData['step'] = 7;
            $session->set($sName, $sData);
            return $this->redirectToRoute('service', ['id' => $service->getId()]);
        }

        $fb = $this->createFormBuilder(null, [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('save', SubmitType::class, [
            'label' => 'Продолжить',
            'attr'  => ['class' => 'btn-success btn-lg'],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $session->set($sName, array_merge($session->get($sName), ['step' => 5]));

            return $this->redirectToRoute('service', ['id' => $service->getId()]);
        }

        $sData['docs'] = isset($sData['docs']) ? $sData['docs'] : [];
        $docs          = $this->em->getRepository('CommonBundle:Image')->createQueryBuilder('i')
            ->andWhere('i.id IN (:ids)')->setParameter('ids', $sData['docs'])
            ->getQuery()->execute()
        ;

        return $this->render('AppBundle:Operator:Service/step4.html.twig', [
            'service'   => $service,
            'form'      => $form->createView(),
            's_data'    => $sData,
            'docs'      => $docs,
            'agreement' => $agreement,
        ]);
    }

    protected function step5(Request $request, Service $service, $sName)
    {
        $agreement = $service->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException();
        }

        $session = $this->get('session');
        $sData   = $session->get($sName);

        if (!empty($sData['medical_center_error_id']) || !$service->getIsEegConclusion()) {
            $sData['step'] = 7;
            $session->set($sName, $sData);
            return $this->redirectToRoute('service', ['id' => $service->getId()]);
        }

        $men = $this->em->getRepository('CommonBundle:Man')->createQueryBuilder('m')
            ->distinct('m.id')
            ->leftJoin('m.brigade', 'b')
            ->leftJoin('m.specialty', 's')
            ->andWhere('s.eeg = :eeg')->setParameter('eeg', true)
            ->andWhere('b.legal_entity = :legal_entity')
            ->setParameter('legal_entity', $this->workplace->getLegalEntity())
            ->getQuery()->getResult();

        $menChoices = [];
        foreach ($men as $man) { /** @var $man \KreaLab\CommonBundle\Entity\Man */
            $menChoices[$man->getFullName()] = $man->getId();
        }

        $eegs = $this->em->getRepository('CommonBundle:Eeg')->createQueryBuilder('e')
            ->andWhere('e.active = :active')->setParameter('active', true)
            ->getQuery()->getResult();

        $eegChoices      = [];
        $eegDescriptions = [];
        foreach ($eegs as $eeg) { /** @var $eeg \KreaLab\CommonBundle\Entity\Eeg */
            $eegChoices[$eeg->getName()]    = $eeg->getId();
            $eegDescriptions[$eeg->getId()] = $eeg->getDescription();
        }

        if (count($men) == 1) {
            /** @var $man \KreaLab\CommonBundle\Entity\Man */
            $man             = $men[0];
            $initData['man'] = $man->getId();
        } else {
            $initData = [];
        }

        if (!empty($sData['parent_id'])) {
            $initData['description'] = $sData['eeg_conclusion'];
            $initData['man']         = $sData['man_id'];
            $initData['name']        = $sData['eeg_id'];
        }

        $fb = $this->createFormBuilder($initData, [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('man', ChoiceType::class, [
            'label'             => 'Врач',
            'placeholder'       => '- Выберите врача -',
            'choices'           => $menChoices,
            'constraints'       => new Assert\NotBlank(),
            'choices_as_values' => true,
            'disabled'          => count($men) == 1 or $sData['parent_id'] ? true : false,
        ]);
        $fb->add('name', ChoiceType::class, [
            'label'             => 'Шаблон заключения',
            'placeholder'       => '- Выберите шаблон заключения -',
            'choices'           => $eegChoices,
            'constraints'       => new Assert\NotBlank(),
            'choices_as_values' => true,
            'disabled'          => $sData['parent_id'],
        ]);
        $fb->add('description', TextareaType::class, [
            'label'       => 'Шаблон заключения',
            'constraints' => new Assert\NotBlank(),
            'attr'        => ['class' => 'ckeditor'],
            'disabled'    => $sData['parent_id'],
        ]);
        $fb->add('save', SubmitType::class, [
            'label' => 'Продолжить',
            'attr'  => ['class' => 'btn-success btn-lg'],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $man = $this->em->getRepository('CommonBundle:Man')->find($form->get('man')->getData());
            $eeg = $this->em->getRepository('CommonBundle:Eeg')->find($form->get('name')->getData());

            $placeholders['{{ Doctor-I }}'] = $man->getFullName();
            $placeholders['{{ Doctor-R }}'] = $man->getFullNameGenitive();
            $description                    = str_replace(
                array_keys($placeholders),
                array_values($placeholders),
                $form->get('description')->getData()
            );

            $session->set($sName, array_merge($session->get($sName), [
                'step'                   => 6,
                'man_id'                 => $man->getId(),
                'man_full_name'          => $man->getFullName(),
                'man_full_name_genitive' => $man->getFullNameGenitive(),
                'eeg_conclusion'         => $description,
                'eeg_id'                 => $eeg->getId(),
                'legal_entity_id'        => $this->workplace->getLegalEntity(),
            ]));

            return $this->redirectToRoute('service', ['id' => $service->getId()]);
        }

        return $this->render('AppBundle:Operator:Service/step5.html.twig', [
            'service'   => $service,
            'form'      => $form->createView(),
            's_data'    => $sData,
            'men'       => $men,
            'eegs'      => $eegDescriptions,
            'agreement' => $agreement,
        ]);
    }

    protected function step6(Request $request, Service $service, $sName)
    {
        $agreement = $service->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException();
        }

        $session = $this->get('session');
        $sData   = $session->get($sName);

        if (!empty($sData['medical_center_error_id']) || !$service->getIsEegConclusion()) {
            $sData['step'] = 7;
            $session->set($sName, $sData);
            return $this->redirectToRoute('service', ['id' => $service->getId()]);
        }

        $fb = $this->createFormBuilder(null, [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('save', SubmitType::class, [
            'label' => 'Продолжить',
            'attr'  => ['class' => 'btn-success btn-lg'],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $session->set($sName, array_merge($session->get($sName), ['step' => 7]));
            return $this->redirectToRoute('service', [
                'id' => $service->getId(),
            ]);
        }

        return $this->render('AppBundle:Operator:Service/step6.html.twig', [
            'service'   => $service,
            'form'      => $form->createView(),
            's_data'    => $sData,
            'agreement' => $agreement,
        ]);
    }

    protected function step7(Request $request, Service $service, $sName)
    {
        $session = $this->get('session');
        $sData   = $session->get($sName);

        $agreement = $service->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException();
        }

        $executorOrGuarantor = $agreement->getExecutorOrGuarantor();
        $referenceType       = $service->getReferenceType();

        $isStamp = !empty($sData['parent_id'])
            || !empty($sData['medical_center_error_id'])
            || !$service->getIsGnoch();

        $data['date_giving'] = isset($sData['date_giving']) ? $sData['date_giving'] : new \DateTime();

        $fb = $this->createFormBuilder($data, [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);

        if ($service->getReferenceType()) {
            if ($referenceType->getIsSerie()) {
                $seriesChoices = $this->em->getRepository('CommonBundle:Blank')
                    ->getCurrentSeries($this->getUserOrSuccessor(), $isStamp, $executorOrGuarantor, $referenceType);

                $fb->add('serie', ChoiceType::class, [
                    'label'             => 'Серия',
                    'placeholder'       => ' - Выберите серию - ',
                    'choices'           => $seriesChoices,
                    'constraints'       => new Assert\NotBlank(),
                    'choices_as_values' => true,
                ]);
            }

            $numberChoices = [];
            if (!empty($request->get('form')['num_blank'])) {
                $numberChoices[$request->get('form')['num_blank']] = $request->get('form')['num_blank'];
            }

            $fb->add('num_blank', ChoiceType::class, [
                'choices'           => $numberChoices,
                'label'             => 'Номер бланка',
                'attr'              => ['bsize' => 9],
                'choices_as_values' => true,
            ]);
        }

        if (empty($sData['medical_center_error_id'])) {
            $fb->add('num_in_journal', TextType::class, [
                'required' => false,
                'label'    => 'Номер в журнале',
                'attr'     => ['bsize' => 3],
            ]);
            $fb->add('cashbox', EntityType::class, [
                'required'      => false,
                'placeholder'   => ' - Выберите кассу -',
                'label'         => 'Касса',
                'class'         => 'CommonBundle:Cashbox',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->andWhere('c.active = :active')->setParameter('active', true)
                        ->andWhere('c.workplace = :workplace')->setParameter('workplace', $this->workplace)
                        ->addOrderBy('c.num');
                },
            ]);
            $fb->add('num_check', TextType::class, [
                'required' => false,
                'label'    => 'Номер чека',
                'attr'     => ['bsize' => 3],
            ]);
            $fb->add('num_shift', TextType::class, [
                'required' => false,
                'label'    => 'Номер смены',
                'attr'     => ['bsize' => 3],
            ]);
        }

        $fb->add('comment', TextType::class, [
            'required' => false,
            'label'    => 'Комментарий',
        ]);

        $fb->add('date_giving', DateType::class, [
            'label'       => 'Дата выдачи справки',
            'widget'      => 'single_text',
            'format'      => 'dd.MM.yyyy',
            'constraints' => new Assert\NotBlank(),
        ]);

        $fb->add('save', SubmitType::class, [
            'label' => 'Сохранить',
            'attr'  => ['class' => 'btn-success btn-lg'],
        ]);
        $form = $fb->getForm();

        if (!empty($sData['parent_id'])) {
            $sData['num_blank']      = null;
            $sData['num_in_journal'] = null;
            $sData['num_check']      = null;
            $sData['num_shift']      = null;
            $sData['comment']        = null;
            $sData['cashbox']        = null;
        }

        $form->setData($sData);

        $form->handleRequest($request);
        if ($request->isMethod('post')) {
            $blank = null;

            if ($service->getReferenceType()) {
                if (empty($request->get('form')['num_blank'])) {
                    $form->get('num_blank')->addError(new FormError('Нет номера'));
                } else {
                    $referenceType = $service->getReferenceType();
                    $serie         = '-';
                    if (!empty($service->getReferenceType()->getIsSerie())) {
                        $serie = $form->get('serie')->getData();
                    }

                    $numBlank = $form->get('num_blank')->getData();

                    /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                    $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy([
                        'legal_entity'   => $executorOrGuarantor,
                        'reference_type' => $referenceType,
                        'serie'          => $serie == '-' ? '' : $serie,
                        'number'         => (int)$numBlank,
                        'stamp'          => $isStamp,
                    ]);

                    if (!$blank) {
                        $form->get('num_blank')->addError(new FormError('Бланк не найден'));
                    } else {
                        $intervals = $this->em->getRepository('CommonBundle:Blank')
                            ->getCurrentIntervals($this->getUserOrSuccessor(), $isStamp, $executorOrGuarantor);

                        $numberInIntervals = BlankIntervals::isExist(
                            $intervals,
                            $blank->getLegalEntity(),
                            $blank->getReferenceType(),
                            $blank->getSerie(),
                            $blank->getLeadingZeros(),
                            $numBlank
                        );

                        if (!$numberInIntervals) {
                            $form->get('num_blank')->addError(new FormError('Бланк не найден'));
                        }
                    }
                }
            }

            if ($form->isValid()) {
                if ($service->getReferenceType() and !$service->getIsGnoch()) {
                    $sData['num_blank'] = $form->get('num_blank')->getData();
                }

                if (empty($sData['medical_center_error_id'])) {
                    $sData['num_in_journal'] = $form->get('num_in_journal')->getData();
                    $sData['num_check']      = $form->get('num_check')->getData();
                    $sData['num_shift']      = $form->get('num_shift')->getData();
                }

                $sData['comment'] = $form->get('comment')->getData();

                $sData['category_id_list'] = isset($sData['category_id_list']) ? $sData['category_id_list'] : [];
                $categories                = $this->em->getRepository('CommonBundle:Category')->createQueryBuilder('c')
                    ->andWhere('c.id IN (:id_list)')->setParameter('id_list', $sData['category_id_list'])
                    ->getQuery()->execute();

                $sData['docs'] = isset($sData['docs']) ? $sData['docs'] : [];
                $docs          = $this->em->getRepository('CommonBundle:Image')->createQueryBuilder('i')
                    ->andWhere('i.id IN (:ids)')->setParameter('ids', $sData['docs'])
                    ->getQuery()->execute();

                $log = new ServiceLog();
                foreach ($categories as $cat) {
                    /** @var $log \KreaLab\CommonBundle\Entity\ServiceLog */
                    $log->addCategory($cat);
                }

                $log->setNum($sData['num']);
                $log->setOperator($this->getUserOrSuccessor());
                $log->setService($service);
                $log->setWorkplace($this->workplace);
                $log->setSum((int)$sData['sum']);
                $log->setBirthday($sData['birthday']);
                $log->setLastName($sData['last_name']);
                $log->setFirstName($sData['first_name']);
                $log->setPatronymic($sData['patronymic']);
                $log->setEegConclusion($sData['eeg_conclusion']);

                if (empty($sData['medical_center_error_id'])) {
                    $log->setCashbox($form->get('cashbox')->getData());
                }

                if ($service->getReferenceType()) {
                    $log->setBlank($blank);
                    $log->setNumBlank($blank->getNumber());
                }

                $parent = null;
                if (!empty($sData['parent_id'])) {
                    $parent = $this->em->find('CommonBundle:ServiceLog', $sData['parent_id']);
                    $log->setParent($parent);
                } elseif (!empty($sData['medical_center_error_id'])) {
                    $medicalCenterError = $this->em->find(
                        'CommonBundle:ServiceLog',
                        $sData['medical_center_error_id']
                    );
                    $log->setMedicalCenterError($medicalCenterError);
                } else {
                    unset($sData['parent_id']);
                    unset($sData['medical_center_error_id']);
                }

                $log->setParams($sData);

                foreach ($docs as $doc) {
                    /** @var $doc \KreaLab\CommonBundle\Entity\Image */
                    $doc->setServiceLog($log);
                    $this->em->persist($doc);
                }

                $log->setDateGiving($form->get('date_giving')->getData());
                $this->em->persist($log);

                if ($service->getReferenceType()) {
                    $oldStatus = $blank->getStatus();
                    $blank->setServiceLog($log);
                    $blank->setServiceLogApplied(new \DateTime());
                    $blank->setStatus('usedByOperator');

                    $env                = $blank->getOperatorEnvelope();
                    $lifeLog            = new BlankLifeLog();
                    $status             = $lifeLog::OO_USED_BY_OPERATOR;
                    $correctBlankNumber = null;
                    if (!empty($sData['medical_center_error_id'])) {
                        if ($service->getIsGnoch()) {
                            $status = $lifeLog::OO_USED_BY_OPERATOR_BY_MEDICAL_ERROR_GNOCH;
                        } else {
                            $status = $lifeLog::OO_USED_BY_OPERATOR_BY_MEDICAL_ERROR;
                        }

                        $correctBlankNumber = $blank->getServiceLog()->getMedicalCenterError()->getBlank()->getNumber();
                    } elseif (!empty($sData['parent_id'])) {
                        if ($parent && $parent->getService()->getIsGnoch()) {
                            $status = $lifeLog::OO_USED_BY_OPERATOR_BY_DUPLICATE_GNOCH;
                        } else {
                            $status = $lifeLog::OO_USED_BY_OPERATOR_BY_DUPLICATE;
                        }

                        $correctBlankNumber = $parent->getBlank()->getNumber();
                    }

                    $lifeLog->setBlank($blank);

                    $lifeLog->setOperationStatus($status);
                    $lifeLog->setWorkplace($this->workplace);
                    $lifeLog->setEnvelopeId($env->getId());
                    $lifeLog->setEnvelopeType('blank_operator_envelope');

                    $lifeLog->setStartStatus($oldStatus);
                    $lifeLog->setEndStatus($blank->getStatus());

                    $lifeLog->setStartUser($this->getUserOrSuccessor());
                    $lifeLog->setEndUser($this->getUserOrSuccessor());

                    $lifeLog->setCorrectBlankNumber($correctBlankNumber);
                    $lifeLog->setServiceName($service->getName());
                    $this->em->persist($lifeLog);
                    $this->em->persist($blank);
                }

                $this->em->flush();

                if (!empty($sData['medical_center_error_id'])) {
                    /** @var $cancelledBlank \KreaLab\CommonBundle\Entity\Blank */
                    $cancelledBlank = $blank->getServiceLog()->getMedicalCenterError()->getBlank();
                    $cancelledBlank->setStatus('cancelledByOperator');
                    $cancelledBlank->setOperatorApplied(null);
                    $this->em->persist($cancelledBlank);

                    if ($service->getIsGnoch()) {
                        $status = BlankLifeLog::OO_CANCELLED_BY_OPERATOR_MEDICAL_ERROR_GNOCH;
                    } else {
                        $status = BlankLifeLog::OO_CANCELLED_BY_OPERATOR_MEDICAL_ERROR;
                    }

                    $env = $blank->getOperatorEnvelope();

                    $lifeLog = new BlankLifeLog();
                    $lifeLog->setBlank($cancelledBlank);
                    $lifeLog->setOperationStatus($status);
                    $lifeLog->setWorkplace($this->workplace);
                    $lifeLog->setEnvelopeId($env->getId());
                    $lifeLog->setEnvelopeType('blank_operator_envelope');

                    $lifeLog->setStartStatus('acceptedByOperator');
                    $lifeLog->setEndStatus($blank->getStatus());

                    $lifeLog->setCorrectBlankNumber($cancelledBlank->getNumber());
                    $lifeLog->setServiceName($service->getName());

                    $lifeLog->setStartUser($this->getUserOrSuccessor());
                    $lifeLog->setEndUser($this->getUserOrSuccessor());

                    $this->em->persist($lifeLog);

                    $this->em->flush();
                }

                $this->workplace->setSum($this->workplace->getSum() + $sData['sum']);
                $this->em->persist($this->workplace);
                $this->em->flush();

                $session->remove($sName);

                if (empty($sData['medical_center_error_id'])) {
                    $cashbox = $form->get('cashbox')->getData();
                    if ($cashbox) {
                        $session->set('cash_box', $cashbox->getNum());
                    }
                }

                $this->addFlash('success', 'Запись об оказании услуги успешно добавлена.');

                return $this->redirectToRoute('services');
            }
        }

        $intervals = [];

        if ($service->getReferenceType()) {
            $intervals = $this->em->getRepository('CommonBundle:Blank')
                ->getCurrentIntervalsFlatten($this->getUserOrSuccessor(), $isStamp, $executorOrGuarantor);

            $intervals = $intervals[$executorOrGuarantor->getId()][$referenceType->getId()];
        }

        return $this->render('AppBundle:Operator:Service/step7.html.twig', [
            'service'        => $service,
            'form'           => $form->createView(),
            's_data'         => $sData,
            'cash_box'       => $session->get('cash_box'),
            'reference_type' => !empty($referenceType) ? $referenceType->getId() : null,
            'init_num_blank' => !empty($numberChoices) ? current($numberChoices) : null,
            'info'           => ['intervals' => $intervals],
            'is_stamp'       => $isStamp,
            'agreement'      => $agreement,
        ]);
    }

    /**
     * @Route("/services/{id}/print-conclusion/", name="service_print_conclusion")
     */
    public function servicePrintConclusionAction($id)
    {
        $service = $this->em->find('CommonBundle:Service', $id);
        if (!$service) {
            throw $this->createNotFoundException();
        }

        $session = $this->get('session');
        $sName   = 'service_'.$service->getId();
        $sData   = $session->get($sName);

        if (!$session->has($sName)
            || ($sData['step'] < 6 && (!isset($sData['step_max']) || $sData['step_max'] < 6))
        ) {
            throw $this->createNotFoundException();
        }

        if ($this->has('profiler')) {
            $this->get('profiler')->disable();
        }

        /** @var  $user \KreaLab\CommonBundle\Entity\User */
        $user = $this->getUser();

        return $this->render('AppBundle:Operator:Service/conclusion.html.twig', [
            'service'               => $service,
            'params'                => $sData,
            'operatorName'          => $user->getFullName(),
            'operatorPowerAttorney' => $user->getPowerAttorney(),
        ]);
    }

    /**
     * @Route("/operator-orders/", name="operator_orders")
     */
    public function ordersAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:Order')->createQueryBuilder('o')
            ->andWhere('o.operator is null')
            ->andWhere('o.status IN (:statuses)')->setParameter('statuses', [
                'createdByTreasurer',
                'forkedByOrderman',
            ])
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Operator/Order:list.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /** @Route("/operator-orders/issue-{id}/", name="operator_orders_issue_order") */
    public function issueOrderAction(Request $request, $id)
    {
        $order = $this->em->getRepository('CommonBundle:Order')->find($id);
        if (!$order || $order->getOperator()) {
            throw $this->createNotFoundException();
        }

        $fb = $this->createFormBuilder([
            'appointment' => $order->getAppointment(),
            'sum'         => $order->getSum(),
        ], [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('acquittanceman', EntityType::class, [
            'label'    => 'Расписчик',
            'class'    => 'CommonBundle:User',
            'disabled' => true,
        ]);
        $fb->add('appointment', TextType::class, [
            'label'    => 'Назначение',
            'disabled' => true,
        ]);
        $fb->add('sum', Measure::class, [
            'label'    => 'Сумма',
            'measure'  => 'руб',
            'attr'     => ['fsize' => 5],
            'disabled' => true,
        ]);
        $fb->add('pin', TextType::class, [
            'label'    => 'Пинкод',
            'required' => true,
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($request->isMethod('post')) {
            if ($order->getPin() != $form->get('pin')->getData()) {
                $form->get('pin')->addError(new FormError('Неверный пинкод'));
            } elseif ($order->getSum() > $this->workplace->getSum()) {
                $form->get('pin')->addError(new FormError('На рабочем месте нет столько наличных'));
            }

            if ($form->isValid()) {
                $order->setOperator($this->getUser());
                $order->setPin($form->get('pin')->getData());
                $order->setWorkplace($this->workplace);
                if ($order->getStatus() == 'forkedByOrderman') {
                    $order->setStatus('issuedForkedByOperator');
                } else {
                    $order->setStatus('issuedByOperator');
                }

                $this->em->persist($order);
                $this->em->flush();

                $this->workplace->setSum($this->workplace->getSum() - $order->getSum());
                $this->em->persist($this->workplace);
                $this->em->flush();

                $this->addFlash('success', 'Ордер обналичен');
                return $this->redirectToRoute('operator_orders');
            }
        }

        return $this->render('AppBundle:Operator/Order:item.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** @Route("/", name="operator_blanks_referenceman_envelopes") */
    public function operatorBlanksReferencemanEnvelopesAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:BlankOperatorEnvelope')->createQueryBuilder('boe')
            ->leftJoin('boe.reference_type', 'rt')->addSelect('rt')
            ->andWhere('boe.operator = :operator')->setParameter('operator', $this->getUser())
            ->andWhere('boe.operator_applied is null')
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

        return $this->render('AppBundle:Operator:envelopes_from_referenceman.html.twig', [
            'pagerfanta'      => $pagerfanta,
            'reference_types' => $referenceTypes,
            'legal_entities'  => $legalEntities,
        ]);
    }

    /** @Route("/envelope-{envelopeId}/", name="operator_blanks_referenceman_envelope") */
    public function operatorBlanksReferencemanEnvelopeAction(Request $request, $envelopeId)
    {
        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->leftJoin('b.operator_envelope', 'oe')->addSelect('oe')
            ->andWhere('oe.id = :envelope_id')->setParameter('envelope_id', $envelopeId)
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Operator:envelope_from_referenceman.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /** @Route("/get-referenceman-envelope-{id}/",
     *      name="operator_blanks_get_referenceman_envelope") */
    public function referencemanBlanksGetReferencemanEnvelopeAction($id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */
        $envelope = $this->em->getRepository('CommonBundle:BlankOperatorEnvelope')->createQueryBuilder('boe')
            ->leftJoin('boe.reference_type', 'rt')->addSelect('rt')
            ->andWhere('boe.operator = :operator')->setParameter('operator', $this->getUser())
            ->andWhere('boe.operator_applied IS NULL')
            ->andWhere('boe.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
        if (!$envelope) {
            throw $this->createNotFoundException();
        }

        $envelope->setOperatorApplied(new \DateTime());
        $this->em->persist($envelope);
        $this->em->flush();

        $blanks = $this->em->getRepository('CommonBundle:Blank')->findBy([
            'operator_envelope' => $envelope,
        ]);
        foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $oldStatus = $blank->getStatus();

            $blank->getLegalEntity();
            $blank->setStatus('acceptedByOperator');
            $blank->setOperatorApplied(new \DateTime());
            $blank->setOperator($this->getUser());
            $blank->setStamp($envelope->getStamp());
            $this->em->persist($blank);

            $lifeLog = new BlankLifeLog();
            $lifeLog->setBlank($blank);
            $lifeLog->setOperationStatus($lifeLog::RO_ACCEPT_BLANK_FROM_REFERENCE);
            $lifeLog->setWorkplace($this->workplace);
            $lifeLog->setEnvelopeId($envelope->getId());
            $lifeLog->setEnvelopeType('blank_operator_envelope');

            $lifeLog->setStartStatus($oldStatus);
            $lifeLog->setEndStatus($blank->getStatus());

            $lifeLog->setStartUser($blank->getReferenceman());
            $lifeLog->setEndUser($this->getUser());

            $this->em->persist($lifeLog);
        }

        $this->em->flush();

        $this->addFlash('success', 'Приняли.');
        return $this->redirectToRoute('operator_blanks_referenceman_envelopes');
    }

    /** @Route("/cancelled-blanks/add-cancelled-blank/",
     *     name="operator_blanks_add_operator_cancelled") */
    public function operatorBlanksAddReferencemanCancelledAction(Request $request)
    {
        $referenceTypes = $this->em->getRepository('CommonBundle:ReferenceType')->createQueryBuilder('rt')
            ->select('rt.id, rt.name')
            ->getQuery()->execute();

        $referenceTypesChoices = [];
        foreach ($referenceTypes as $referenceType) {
            $referenceTypesChoices[$referenceType['name']] = $referenceType['id'];
        }

        $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT b.serie')
            ->orderBy('b.serie')
            ->andWhere('b.serie IS NOT NULL')
            ->getQuery()->execute();

        $seriesChoices = [];
        foreach ($series as $serie) {
            $seriesChoices[$serie['serie']] = $serie['serie'];
        }

        $fb = $this->createFormBuilder(null, [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('legal_entity', EntityType::class, [
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
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
        $fb->add('desc_cancelled', TextType::class, [
            'label'    => 'Примечание',
            'required' => false,
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy([
                'serie'          => $seriesChoices[$form->get('serie')->getData()],
                'number'         => (int)$form->get('number')->getData(),
                'reference_type' => $form->get('reference_type')->getData(),
                'legal_entity'   => $form->get('legal_entity')->getData(),
                'status'         => 'acceptedByOperator',
                'operator'       => $this->getUser(),
                'leading_zeros'  => strlen($form->get('number')->getData()),
            ]);
            if ($blank) {
                $oldStatus = $blank->getStatus();
                $blank->setStatus('cancelledByOperator');
                $blank->setOperatorApplied(null);
                $blank->setDescCancelled($form->get('desc_cancelled')->getData());
                $this->em->persist($blank);

                $env     = $blank->getOperatorEnvelope();
                $lifeLog = new BlankLifeLog();
                $lifeLog->setBlank($blank);
                $lifeLog->setOperationStatus($lifeLog::OO_CANCELLED_BY_OPERATOR);
                $lifeLog->setWorkplace($this->workplace);
                $lifeLog->setEnvelopeId($env->getId());
                $lifeLog->setEnvelopeType('blank_operator_envelope');

                $lifeLog->setStartStatus($oldStatus);
                $lifeLog->setEndStatus($blank->getStatus());

                $lifeLog->setStartUser($this->getUserOrSuccessor());
                $lifeLog->setEndUser($this->getUser());

                $this->em->persist($lifeLog);

                $this->em->flush();
                $this->addFlash('success', 'Принято');
                return $this->redirectToRoute('operator_blanks_add_operator_cancelled');
            } else {
                $this->addFlash('danger', 'Не найден');
            }
        }

        $blanks = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT b.serie, (b.reference_type) as reference_type, (b.legal_entity) as legal_entity')
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByOperator')
          //  ->andWhere('b.legal_entity = :legal_entity')->setParameter('legal_entity', $this->workplace)
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

        return $this->render('AppBundle:Operator:cancel_blank.html.twig', [
            'form'                    => $form->createView(),
            'blanks'                  => $blanksFilter,
            'reference_types'         => $referenceTypesIdKeys,
            'serie_selected'          => $form->get('serie')->getViewData(),
            'reference_type_selected' => $form->get('reference_type')->getViewData(),

        ]);
    }

    /** @Route("/cancelled-blanks/", name="operator_blanks_cancelled_blanks") */
    public function operatorBlanksCancelledBlanksAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->leftJoin('b.service_log', 'sl')->addSelect('sl')
            ->andWhere('b.status = :status')->setParameter('status', 'cancelledByOperator')
            ->andWhere('b.operator_applied is NULL')
            ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUser())
            ->orderBy('b.number')
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Operator:cancelled_blanks.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /** @Route("/cancelled-blanks/revert-blank-{id}/", name="operator_blanks_revert_cancelled_blank") */
    public function operatorBlanksRevertCancelledBlankAction($id)
    {
        /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
        $blank = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->leftJoin('b.serviceLog', 'sl')->addSelect('sl')
            ->andWhere('b.status = :status')->setParameter('status', 'cancelledByOperator')
            ->andWhere('b.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
        if ($blank) {
            $oldStatus = $blank->getStatus();
            $blank->setStatus('acceptedByOperator');
            $blank->setOperatorApplied(new \DateTime());

            $this->em->persist($blank);

            $env     = $blank->getOperatorEnvelope();
            $lifeLog = new BlankLifeLog();
            $lifeLog->setBlank($blank);
            $lifeLog->setOperationStatus($lifeLog::OO_ACCEPT_CANCELED_BLANK);
            $lifeLog->setWorkplace($this->workplace);
            $lifeLog->setEnvelopeId($env->getId());
            $lifeLog->setEnvelopeType('blank_operator_envelope');

            $lifeLog->setStartStatus($oldStatus);
            $lifeLog->setEndStatus($blank->getStatus());

            $lifeLog->setStartUser($this->getUserOrSuccessor());
            $lifeLog->setEndUser($this->getUser());

            $this->em->persist($lifeLog);

            $this->em->flush();
            $this->addFlash('success', 'Вернули.');
        } else {
            $this->addFlash('danger', 'Не нашли.');
        }

        return $this->redirectToRoute('operator_blanks_cancelled_blanks');
    }

    /**
     * @Route("/get-blank-numbers/",
     *      name="operator_blanks_get_blank_numbers")
     */
    public function operatorBlanksGetBlankNumbers(Request $request)
    {
        $refType     = $request->get('reference_type');
        $serie       = $request->get('serie', '-');
        $isStamp     = $request->get('stamp', true);
        $serviceId   = $request->get('serviceId', 0);
        $workplaceId = $request->get('workplaceId', $this->workplace->getId());

        $referenceType = $this->em->getRepository('CommonBundle:ReferenceType')->find(intval($refType));

        $service = $this->em->getRepository('CommonBundle:Service')->find(intval($serviceId));
        if (!$service) {
            throw $this->createNotFoundException('No service');
        }

        $workplace = $this->em->getRepository('CommonBundle:Workplace')->find(intval($workplaceId));
        if (!$workplace) {
            throw $this->createNotFoundException('No workplace');
        }

        /** @var $agreement \KreaLab\CommonBundle\Entity\Agreement */
        $agreement = $this->em->getRepository('CommonBundle:Agreement')->createQueryBuilder('a')
            ->andWhere('a.service = :serviceId')->setParameter('serviceId', $serviceId)
            ->andWhere('a.workplace = :workplaceId')->setParameter('workplaceId', $workplaceId)
            ->andWhere('a.type IS NOT NULL')
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$agreement) {
            throw $this->createNotFoundException('no agreement');
        }

        $executorOrGuarantor = $agreement->getExecutorOrGuarantor();

        $numbers = $this->em->getRepository('CommonBundle:Blank')
            ->getCurrentNumbers($this->getUserOrSuccessor(), $isStamp, $executorOrGuarantor, $referenceType, $serie);

        $numbersFiltered = [];
        foreach ($numbers as $number) {
            $strlen = strlen($request->get('number'));
            $substr = substr($number, strlen($number) - $strlen);
            if ($substr == $request->get('number')) {
                $numbersFiltered[] = ['number' => $number];
            }
        }

        $pagerfanta = new Pagerfanta(new ArrayAdapter($numbersFiltered));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        $items  = [];
        $blanks = $pagerfanta->getCurrentPageResults();

        foreach ($blanks as $blank) {
            $items[] = [
                'id'   => $blank['number'],
                'text' => $blank['number'],
            ];
        }

        return new JsonResponse([
            'items'       => $items,
            'total_count' => $pagerfanta->getNbResults(),
        ]);
    }

    /** @Route("/on-hands/", name="operator_blanks_on_hands") */
    public function onHandsAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('b, count(b.id) as amount')
            ->leftJoin('b.reference_type', 'rt')->addSelect('rt')
            ->leftJoin('b.legal_entity', 'le')->addSelect('le')
            ->groupBy('b.serie, b.reference_type, b.legal_entity, b.stamp')
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByOperator')
            ->andWhere('b.operator_applied is NOT NULL')
            ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUser())
        ;

        $fb = $this->createFormBuilder([
            'csrf_protection'    => false,
            'translation_domain' => false,
        ]);

        $fb->add('legal_entity', EntityType::class, [
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'choice_label'  => 'nameAndShortName',
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->andWhere('le.active = :active')->setParameter('active', true);
            },
        ]);
        $fb->add('reference_type', EntityType::class, [
            'label'       => 'Тип справки',
            'class'       => 'CommonBundle:ReferenceType',
            'placeholder' => ' - Выберите тип справки - ',
            'required'    => false,
        ]);

        $stampChoices['- Выберите статус печати -'] = '';
        $stampChoices['Да']                         = 'with_stamp';
        $stampChoices['Нет']                        = 'with_no_stamp';

        $fb->add('stamp', ChoiceType::class, [
            'label'             => 'Печать',
            'choices'           => $stampChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);

        $fb->setMethod('get');
        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
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

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb, false));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        $blanks    = $pagerfanta->getCurrentPageResults();
        $blanksOut = [];

        foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $amount      = $blank['amount'];
            $blank       = $blank[0];
            $blanksOut[] = [
                'legal_entity'   => $blank->getLegalEntity(),
                'reference_type' => $blank->getReferenceType(),
                'serie'          => empty($blank->getSerie()) ? '-' : $blank->getSerie(),
                'amount'         => $amount,
                'stamp'          => $blank->getStamp(),
            ];
        }

        return $this->render('AppBundle:Operator:on_hands_reference_types.html.twig', [
            'pagerfanta'  => $pagerfanta,
            'blanks'      => $blanksOut,
            'filter_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/on-hands/view/",
     *     name="operator_blanks_on_hands_view") */
    public function onHandsViewAction(Request $request)
    {
        $legalEntityId   = $request->get('legalEntityId');
        $referenceTypeId = $request->get('referenceTypeId');
        $serie           = $request->get('serie');
        $stamp           = $request->get('stamp');

        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->leftJoin('b.reference_type', 'rt')->addSelect('rt')
            ->andWhere('rt.id = :reference_type_id')->setParameter('reference_type_id', intval($referenceTypeId))
            ->andWhere('b.legal_entity = :legal_entity')->setParameter('legal_entity', $legalEntityId)
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByOperator')
            ->andWhere('b.operator_applied is NOT NULL')
            ->andWhere('b.stamp = :stamp')->setParameter('stamp', $stamp)
            ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUser())
            ->addOrderBy('b.serie')
            ->addOrderBy('b.number')
        ;

        if ($serie != '-') {
            $qb->andWhere('b.serie = :serie')->setParameter('serie', $serie);
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Operator:on_hands_reference_type.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /**
     * @Route("/agreement/on-hands/view-{service}/",
     *     name="operator_blanks_agreement_on_hands_view") */
    public function agreementOnHandsViewAction(Request $request, Service $service)
    {
        $agreement = $service->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException('No agreement');
        }

        $executor = $agreement->getExecutor();
        if (!$executor) {
            throw $this->createNotFoundException('No executor');
        }

        $isStamp = !$service->getIsGnoch();
        $refType = $service->getReferenceType();

        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUserOrSuccessor())
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByOperator')
            ->andWhere('b.stamp = :stamp')->setParameter('stamp', $isStamp)
            ->andWhere('b.legal_entity = :legal_entity')
            ->setParameter('legal_entity', $agreement->getExecutorOrGuarantor())
            ->andWhere('b.reference_type = :ref_type')->setParameter('ref_type', $refType)
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(100);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Operator:agreement_on_hands.html.twig', [
            'pagerfanta' => $pagerfanta,
            'service'    => $service,
        ]);
    }

    /**
     * @Route("/agreement/current/view-{service}/",
     *     name="operator_blanks_agreement_current_view") */
    public function agreementCurrentViewAction(Request $request, Service $service)
    {
        $agreement = $service->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException('No agreement');
        }

        $executor = $agreement->getExecutor();
        if (!$executor) {
            throw $this->createNotFoundException('No executor');
        }

        $isStamp = !$service->getIsGnoch();
        $refType = $service->getReferenceType();

        $uqb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUserOrSuccessor())
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByOperator')
            ->andWhere('b.stamp = :stamp')->setParameter('stamp', $isStamp)
            ->andWhere('b.legal_entity = :legal_entity')
            ->setParameter('legal_entity', $agreement->getExecutorOrGuarantor())
            ->andWhere('b.reference_type = :ref_type')->setParameter('ref_type', $refType)
        ;

        $qb   = clone $uqb;
        $data = $qb
            ->select('DISTINCT (b.reference_type) ref_type_id, b.serie, oe.id envelope_id, oe.operator_applied')
            ->leftJoin('b.operator_envelope', 'oe')
            ->orderBy('oe.operator_applied')
            ->getQuery()->getArrayResult();

        $envelopes = [];
        foreach ($data as $value) {
            if (!isset($envelopes[$value['ref_type_id']])) {
                $envelopes[$value['ref_type_id']] = $value;
            }
        }

        if (!isset($envelopes[$refType->getId()])) {
            throw $this->createNotFoundException('No envelope');
        }

        $qb = clone $uqb;
        $qb
            ->andWhere('b.reference_type = :ref_type')->setParameter('ref_type', $refType->getId())
            ->andWhere('b.operator_envelope = :envelope')
            ->setParameter('envelope', $envelopes[$refType->getId()]['envelope_id'])
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(100);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Operator:agreement_current.html.twig', [
            'pagerfanta' => $pagerfanta,
            'service'    => $service,
        ]);
    }

    /** @Route("/on-hands/lost-{id}/", name="operator_blanks_on_hands_lost") */
    public function onHandsLostAction(Request $request, $id)
    {
        /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
        $blank = $this->em->getRepository('CommonBundle:Blank')->findOneBy([
            'id'       => $id,
            'operator' => $this->getUser(),
            'status'   => 'acceptedByOperator',
        ]);
        if (!$blank) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('post')) {
            $oldStatus = $blank->getStatus();
            $blank->setStatus('lost');
            $this->em->persist($blank);

            $env     = $blank->getOperatorEnvelope();
            $lifeLog = new BlankLifeLog();
            $lifeLog->setBlank($blank);
            $lifeLog->setOperationStatus($lifeLog::OO_LOST_BLANK);
            $lifeLog->setWorkplace($this->workplace);
            $lifeLog->setEnvelopeId($env->getId());
            $lifeLog->setEnvelopeType('blank_operator_envelope');

            $lifeLog->setStartStatus($oldStatus);
            $lifeLog->setEndStatus($blank->getStatus());

            $lifeLog->setStartUser($this->getUserOrSuccessor());
            $lifeLog->setEndUser($this->getUser());

            $this->em->persist($lifeLog);

            $this->em->flush();

            $this->addFlash('success', 'Бланк отмечен как утерянный');
            return $this->redirectToRoute('operator_blanks_on_hands_view', [
                'legalEntityId'   => $blank->getLegalEntity()->getId(),
                'referenceTypeId' => $blank->getReferenceType()->getId(),
                'serie'           => $blank->getSerie() ? $blank->getSerie() : '-',
            ]);
        }

        $settingsRepo = $this->em->getRepository('AdminSkeletonBundle:Setting');
        $title        = $settingsRepo->get('operator_blanks_on_hands_lost_title', '');
        $message      = $settingsRepo->get('operator_blanks_on_hands_lost_text', '');

        return $this->render('AppBundle:Operator:on_hands_lost.html.twig', [
            'title'   => $title,
            'message' => $message,
            'blank'   => $blank,
        ]);
    }

    /** @Route("/current-blanks/", name="operator_current_blanks") */
    public function currentBlanksAction()
    {
        $stampList = [true, false];

        $rows               = [];
        $currentLegalEntity = $this->workplace->getLegalEntity();

        foreach ($stampList as $isStamp) {
            $intervals = $this->em->getRepository('CommonBundle:Blank')
                ->getCurrentIntervals($this->getUserOrSuccessor(), $isStamp);

            $refTypes = $this->em->getRepository('CommonBundle:ReferenceType')->createQueryBuilder('rt')
                ->select('rt.id, rt.name')
                ->getQuery()->getArrayResult();

            $refTypeArr = [];
            foreach ($refTypes as $refType) {
                $refTypeArr[$refType['id']] = $refType;
            }

            if (isset($intervals[$currentLegalEntity->getId()])) {
                foreach ($intervals[$currentLegalEntity->getId()] as $refTypeId => $curSeries) {
                    foreach ($curSeries as $serieName => $curIntervals) {
                        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                            ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUser())
                            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByOperator')
                            ->andWhere('b.legal_entity = :legal_entity')
                            ->setParameter('legal_entity', $currentLegalEntity)
                            ->andWhere('b.reference_type = :reference_type')
                            ->setParameter('reference_type', $refTypeId)
                            ->andWhere('b.stamp = :stamp')->setParameter('stamp', $isStamp)
                            ->setMaxResults(1)
                        ;

                        if ($serieName != '-') {
                            $qb->andWhere('b.serie = :serie')->setParameter('serie', $serieName);
                        } else {
                            $qb->andWhere('b.serie = :serie')->setParameter('serie', '');
                        }

                        $rows[] = [
                            'legal_entity'   => $currentLegalEntity,
                            'reference_type' => $refTypeArr[$refTypeId],
                            'serie'          => $serieName,
                            'intervals'      => $curIntervals,
                            'stamp'          => (int)$isStamp,
                        ];
                    }
                }
            }
        }

        return $this->render('@App/Operator/current_blanks.html.twig', ['rows' => $rows]);
    }

    /** @Route("/current/view/", name="operator__current_blanks__view") */
    public function currentBlanksViewAction(Request $request)
    {
        $referenceTypeId = $request->get('referenceTypeId');
        $serie           = $request->get('serie');
        $stamp           = $request->get('stamp');

        if ($serie == '-') {
            $serie = '';
        }

        $currentLegalEntity = $this->workplace->getLegalEntity();

        $data = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT (b.reference_type) ref_type_id, b.serie, oe.id envelope_id, oe.operator_applied')
            ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUser())
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByOperator')
            ->andWhere('b.legal_entity = :legal_entity')->setParameter('legal_entity', $currentLegalEntity)
            ->andWhere('b.stamp = :stamp')->setParameter('stamp', $stamp)
            ->leftJoin('b.operator_envelope', 'oe')
            ->orderBy('oe.operator_applied')
            ->getQuery()->execute();

        $envelopes = [];
        foreach ($data as $value) {
            if (!isset($envelopes[$value['ref_type_id']])) {
                $envelopes[$value['ref_type_id']] = $value;
            }
        }

        $pagerfanta = null;

        if (isset($envelopes[$referenceTypeId])) {
            $envelopeData = $envelopes[$referenceTypeId];

            $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUser())
                ->andWhere('b.status = :status')->setParameter('status', 'acceptedByOperator')
                ->andWhere('b.legal_entity = :legal_entity')->setParameter('legal_entity', $currentLegalEntity)
                ->andWhere('b.reference_type = :reference_type')->setParameter('reference_type', $referenceTypeId)
                ->andWhere('b.serie = :serie')->setParameter('serie', $serie)
                ->andWhere('b.stamp = :stamp')->setParameter('stamp', $stamp)
                ->andWhere('b.operator_envelope = :envelope')->setParameter('envelope', $envelopeData['envelope_id'])
            ;

            $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
            $pagerfanta->setMaxPerPage(20);
            $pagerfanta->setCurrentPage($request->get('page', 1));
        }

        return $this->render('AppBundle:Operator:current_view.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /** @Route("/medical-center-errors/", name="medical_center_errors_search") */
    public function medicalCenterErrors(Request $request)
    {
        $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->addSelect('b.serie')
            ->leftJoin('b.service_log', 'sl')->addSelect('sl')
            ->leftJoin('sl.medical_center_corrects', 'mcc')->addSelect('mcc')
            ->andWhere('mcc.id IS NULL')
            ->leftJoin('sl.children', 'ch')->addSelect('ch')
            ->andWhere('ch.id is NULL')
            ->andWhere('b.status = :status')->setParameter('status', 'usedByOperator')
            ->groupBy('b.serie')
            ->orderBy('b.serie')
            ->getQuery()->getArrayResult();

        $seriesChoices = [];
        foreach ($series as $serie) {
            if ($serie['serie'] != '') {
                $seriesChoices[$serie['serie']] = $serie['serie'];
            }
        }

        $fb = $this->createFormBuilder([], [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('reference_type', EntityType::class, [
            'label'         => 'Тип бланка',
            'class'         => 'CommonBundle:ReferenceType',
            'placeholder'   => ' - Выберите тип бланка - ',
            'constraints'   => new Assert\NotBlank(),
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('rt')
                    ->innerJoin('rt.blanks', 'b', 'WITH', 'b.status = :status')
                    ->setParameter('status', 'usedByOperator')
                    ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUser())
                ;
            },
        ]);

        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'placeholder'       => ' - Выберите - ',
            'choices'           => $seriesChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);
        $fb->add('number', TextType::class, [
            'label'       => 'Номер бланка',
            'attr'        => ['bsize' => 9],
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('date_giving', DateType::class, [
            'label'       => 'Дата выдачи',
            'placeholder' => '--',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('find', SubmitType::class, [
            'label' => 'Найти',
            'attr'  => ['class' => 'btn-success btn-lg'],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $qb = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
                ->leftJoin('sl.blank', 'b')->addSelect('b')
                ->leftJoin('sl.service', 's')->addSelect('s')
                ->leftJoin('s.agreements', 'a')->addSelect('a')
                ->andWhere('a.workplace = :workplace')->setParameter('workplace', $this->workplace)
                ->andWhere('b.number = :number')->setParameter('number', (int)$form->get('number')->getData())
                ->andWhere('sl.date_giving = :date_giving')
                ->leftJoin('sl.medical_center_corrects', 'mcc')->addSelect('mcc')
                ->andWhere('mcc.id IS NULL')
                ->leftJoin('sl.children', 'ch')->addSelect('ch')
                ->andWhere('ch.id is NULL')
                ->setParameter('date_giving', $form->get('date_giving')->getData())
            ;

            $referenceType = $this->em->getRepository('CommonBundle:ReferenceType')
                ->find($form->get('reference_type')->getData());

            if ($referenceType->getIsSerie()) {
                $qb->andWhere('b.serie = :serie')->setParameter('serie', $form->get('serie')->getData());
            }

            $serviceLog = $qb->getQuery()->getOneOrNullResult();

            return $this->render('AppBundle:Operator/MedicalCenterErrors:found.html.twig', [
                'serviceLog' => $serviceLog,
            ]);
        }

        $referenceTypes = $this->em->getRepository('CommonBundle:ReferenceType')->createQueryBuilder('rt')
            ->getQuery()->getArrayResult();

        $referenceTypesIdKeys = [];
        foreach ($referenceTypes as $referenceType) {
            $referenceTypesIdKeys[$referenceType['id']] = $referenceType;
        }

        return $this->render('AppBundle:Operator/MedicalCenterErrors:search.html.twig', [
            'form'            => $form->createView(),
            'reference_types' => $referenceTypesIdKeys,
        ]);
    }

    /**
     * @Route("/medical-center-errors/init-{id}/",
     *     name="medical_center_errors_init")
     */
    public function medicalCenterErrorInitAction($id)
    {
        /** @var $serviceLog \KreaLab\CommonBundle\Entity\ServiceLog */
        $serviceLog = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
            ->leftJoin('sl.service', 's')->addSelect('s')
            ->leftJoin('sl.blank', 'b')->addSelect('b')
            ->leftJoin('sl.medical_center_corrects', 'mcc')->addSelect('mcc')
            ->andWhere('sl.id = :id')->setParameter('id', $id)
            ->andWhere('mcc.id IS NULL')
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$serviceLog) {
            throw $this->createNotFoundException('No service log');
        }

        $service = $serviceLog->getService();

        $agreement = $service->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException('no agreement');
        }

        $executorOrGuarantor = $agreement->getExecutorOrGuarantor();

        if ($service->getReferenceType()) {
            $isStamp      = true;
            $refType      = $service->getReferenceType();
            $blanksAmount = $this->em->getRepository('CommonBundle:Blank')
                ->getCurrentIntervalsAmount($this->getUserOrSuccessor(), $isStamp, $executorOrGuarantor, $refType);

            if (!$blanksAmount) {
                throw $this->createNotFoundException('Нет бланков');
            }
        }

        $sName   = 'service_'.$service->getId();
        $session = $this->get('session');
        $session->remove($sName);

        $numParts     = [];
        $numParts[]   = $executorOrGuarantor->getId();
        $numParts[]   = $this->workplace->getId();
        $numParts[]   = $id;
        $numParts[]   = time() - strtotime('2016-01-01');
        $sData['num'] = implode('-', $numParts);

        $params = $serviceLog->getParams();
        if (isset($params['sum_online'])) {
            unset($params['sum_online']);
        }

        $sData                            = array_merge($sData, $params);
        $sData['year']                    = date('Y', strtotime($params['birthday']->date));
        $sData['month']                   = date('m', strtotime($params['birthday']->date));
        $sData['day']                     = date('d', strtotime($params['birthday']->date));
        $sData['sum_type']                = 'medical_center_error';
        $sData['sum']                     = 0;
        $sData['discount']                = 0;
        $sData['medical_center_error_id'] = $serviceLog->getId();
        $sData['step']                    = 1;
        $sData['parent_id']               = false;
        $sData['revisit']                 = false;
        $sData['date_giving']             = $serviceLog->getDateGiving();
        $session->set($sName, $sData);

        return $this->redirectToRoute('service', ['id' => $service->getId()]);
    }

    /**
     * @Route("/services-upload-documents-search/",
     *     name="services_upload_documents_search")
     */
    public function servicesUploadDocumentsSearch(Request $request)
    {
        $fb = $this->container->get('form.factory')->createNamedBuilder(null, FormType::class, null, [
            'csrf_protection'    => false,
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('last_name', TextType::class, [
            'label'       => 'Фамилия',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('first_name', TextType::class, [
            'label'       => 'Имя',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('patronymic', TextType::class, [
            'label'       => 'Отчество',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('birthday', BirthdayType::class, [
            'label' => 'Дата рождения',
            'years' => range(1930, date('Y')),
        ]);
        $fb->setMethod('get');
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $serviceLogs = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
                ->leftJoin('sl.docs', 'd')
                ->having('COUNT(d.id) = 0')
                ->groupBy('sl')
                ->andWhere('sl.last_name = :last_name')
                ->setParameter('last_name', $form->get('last_name')->getData())
                ->andWhere('sl.first_name = :first_name')
                ->setParameter('first_name', $form->get('first_name')->getData())
                ->andWhere('sl.patronymic = :patronymic')
                ->setParameter('patronymic', $form->get('patronymic')->getData())
                ->andWhere('sl.birthday = :birthday')
                ->setParameter('birthday', $form->get('birthday')->getData())
                ->getQuery()->execute();

            return $this->render('AppBundle:Operator:Service/upload_documents_found.html.twig', [
                'service_logs' => $serviceLogs]);
        }

        return $this->render('AppBundle:Operator:Service/upload_documents_search.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/services-upload-documents-search/init-{id}/", name="service_upload_documents_init")
     */
    public function serviceUploadDocumentsInitAction($id)
    {
        /** @var $serviceLog \KreaLab\CommonBundle\Entity\ServiceLog */
        $serviceLog = $this->em->find('CommonBundle:ServiceLog', $id);
        if (!$serviceLog || count($serviceLog->getDocs())) {
            throw $this->createNotFoundException();
        }

        $sName   = 'service_'.$serviceLog->getService()->getId();
        $session = $this->get('session');
        $session->remove($sName);

        return $this->redirectToRoute('service_upload_documents_upload', ['id' => $id]);
    }

    /**
     * @Route("/services-upload-documents-search/upload-{id}/", name="service_upload_documents_upload")
     */
    public function serviceUploadDocumentsUploadAction(Request $request, $id)
    {
        /** @var $serviceLog \KreaLab\CommonBundle\Entity\ServiceLog */
        $serviceLog = $this->em->find('CommonBundle:ServiceLog', $id);
        if (!$serviceLog || count($serviceLog->getDocs())) {
            throw $this->createNotFoundException();
        }

        $fb   = $this->createFormBuilder(null, [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $form = $fb->getForm();
        $form->handleRequest($request);

        $sName             = 'service_'.$serviceLog->getService()->getId();
        $session           = $this->get('session');
        $sData             = $session->get($sName);
        $sData['docs']     = isset($sData['docs']) ? $sData['docs'] : [];
        $sData['step']     = 4;
        $sData['step_max'] = 4;
        $session->set($sName, $sData);

        if ($form->isValid()) {
            foreach ($sData['docs'] as $docId) { /** @var $doc \KreaLab\CommonBundle\Entity\Image */
                $doc = $this->em->getRepository('CommonBundle:Image')->find($docId);
                if ($doc) {
                    $doc->setServiceLog($serviceLog);
                    $this->em->persist($doc);
                }
            }

            $this->em->flush();

            $this->addFlash('success', 'Загрузили');
            $session->remove($sName);
            return $this->redirectToRoute('services_upload_documents_search');
        }

        $docs = $this->em->getRepository('CommonBundle:Image')->findBy(['id' => $sData['docs']]);

        return $this->render('AppBundle:Operator:Service/upload_documents_init.html.twig', [
            'form'        => $form->createView(),
            'service'     => $serviceLog->getService(),
            'docs'        => $docs,
            'service_log' => $serviceLog,
        ]);
    }

    /**
     * @Route("/envelopes-of-transfered-blanks-to-stockman-or-referenceman/",
     *     name="operator_blanks_envelopes_of_transfered_blanks_to_referenceman")
     */
    public function operatorBlanksEnvelopesOfTransferedBlanksToStockmanOrReferenceman()
    {
        $operatorReferencemanEnvelopes = $this->em
            ->getRepository('CommonBundle:BlankOperatorReferencemanEnvelope')
            ->createQueryBuilder('bore')
            ->leftJoin('bore.blanks', 'b', 'WITH', 'b.status = :status')
            ->setParameter('status', 'appointedToReferencemanFromOperator')
            ->addSelect('b')
            ->andWhere('bore.referenceman_applied IS NULL')
            ->getQuery()->execute();

        return $this->render(
            'AppBundle:Operator:envelopes_of_transfered_blanks_to_referenceman.html.twig',
            ['envelopes' => $operatorReferencemanEnvelopes]
        );
    }

    /**
     * @Route("/transfer-blanks-to-referenceman/",
     *     name="operator_blanks_transfer_blanks_to_referenceman")
     */
    public function operatorBlanksTransferBlanksToReferenceman(Request $request)
    {
        $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT(b.serie) AS serie')
            ->orderBy('serie')
            ->andWhere('b.status IN (:statuses)')
            ->setParameter('statuses', ['acceptedByOperator', 'replacedBecauseNoStampByOperator'])
            ->getQuery()->execute();

        $seriesChoices = [];
        foreach ($series as $serie) {
            $seriesChoices[$serie['serie']] = $serie['serie'];
        }

        $fb = $this->get('form.factory')->createNamedBuilder('', FormType::class, null, [
            'translation_domain' => false,
            'csrf_protection'    => false,
        ]);
        $fb->add('legal_entity', EntityType::class, [
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'mapped'        => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->andWhere('le.active = :active')->setParameter('active', true);
            },
        ]);
        $fb->add('referenceman', EntityType::class, [
            'label'         => 'Кладовщик',
            'placeholder'   => ' - Выберите кладовщика - ',
            'constraints'   => new Assert\NotBlank(),
            'class'         => 'CommonBundle:User',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.active = :active')->setParameter('active', true)
                    ->andWhere('u.roles LIKE :role_referenceman')
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
            'placeholder' => ' - Выберите тип справки - ',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'placeholder'       => ' - Выберите серию - ',
            'choices'           => $seriesChoices,
            'constraints'       => new Assert\NotBlank(),
            'choices_as_values' => true,
        ]);
        $fb->add('first_num', TextType::class, [
            'label'       => 'Номер первого бланка',
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
        $fb->add('amount', IntegerType::class, [
            'label'       => 'Количество',
            'attr'        => ['min' => 1],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\GreaterThan(0),
            ],
        ]);
        $fb->setMethod('get');
        $form = $fb->getForm();
        $form->handleRequest($request);

        $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.status IN (:statuses)')
            ->setParameter('statuses', ['acceptedByOperator', 'replacedBecauseNoStampByOperator'])
            ->andWhere('b.operator_applied is not null')
            ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUser())
            ->andWhere('b.reference_type = :reference_type')
            ->setParameter('reference_type', $request->get('reference_type'))
            ->andWhere('b.legal_entity = :legal_entity')
            ->setParameter('legal_entity', $request->get('legal_entity'))
            ->andWhere('b.number >= :first_num')->setParameter('first_num', (int)$request->get('first_num'))
            ->andWhere('b.operator_referenceman_envelope is null')
            ->andWhere('b.leading_zeros = :leading_zeros')
            ->setParameter('leading_zeros', strlen($request->get('first_num')))
            ->addOrderBy('b.number')
        ;

        if (!empty($form->get('reference_type')->getData())) {
            $referenceType = $this->em->getRepository('CommonBundle:ReferenceType')
                ->find($request->get('reference_type'));

            if ($referenceType->getIsSerie()) {
                $qb->andWhere('b.serie = :serie')->setParameter('serie', $request->get('serie'));
            }
        }

        $blanks = $qb->setMaxResults($request->get('amount'))->getQuery()->execute();

        $blankChoices = [];
        foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $blankChoices[$blank->getId()] = $blank->getId();
        }

        $fb2 = $this->get('form.factory')
            ->createNamedBuilder('form2', FormType::class, null, ['translation_domain' => false]);
        $fb2->add('blanks', ChoiceType::class, [
            'choices_as_values' => true,
            'multiple'          => true,
            'expanded'          => true,
            'choices'           => $blankChoices,
        ]);
        $fb2->add('save', SubmitType::class, [
            'label' => 'Передать',
            'attr'  => ['class' => 'btn btn-success pull-right'],
        ]);

        $form2 = $fb2->getForm();
        $form2->handleRequest($request);

        if ($form2->isValid()) {
            $this->em->beginTransaction();
            /** @var $referenceman \KreaLab\CommonBundle\Entity\User */
            $referenceman  = $this->em->find('CommonBundle:User', $request->get('referenceman'));
            $referenceType = $this->em->find('CommonBundle:ReferenceType', $request->get('reference_type'));
            $serie         = $request->get('serie');
            $chosenBlanks  = $form2->get('blanks')->getData();
            $blankIds      = array_values($chosenBlanks);
            $amount        = count($chosenBlanks);

            $qb = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->andWhere('b.id IN (:ids)')->setParameter('ids', $blankIds)
                ->andWhere('b.status IN (:statuses)')
                ->setParameter('statuses', ['acceptedByOperator', 'replacedBecauseNoStampByOperator'])
                ->andWhere('b.operator_applied is not null')
                ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUser())
                ->andWhere('b.reference_type = :reference_type')
                ->setParameter('reference_type', $request->get('reference_type'))
                ->setParameter('legal_entity', $request->get('legal_entity'))
                ->andWhere('b.legal_entity = :legal_entity')
                ->andWhere('b.number >= :first_num')->setParameter('first_num', (int)$request->get('first_num'))
                ->andWhere('b.operator_referenceman_envelope is null')
                ->addOrderBy('b.number')
                ->setMaxResults($amount)
            ;

            if ($referenceType->getIsSerie()) {
                $qb->andWhere('b.serie = :serie')->setParameter('serie', $request->get('serie'));
            }

            $blanksValid = $qb->getQuery()->getResult();

            if (!empty($blanksValid)) {
                $envelope = new BlankOperatorReferencemanEnvelope();
                $envelope->setReferenceman($referenceman);
                $envelope->setOperator($this->getUser());
                /** @var $firstBlank \KreaLab\CommonBundle\Entity\Blank */
                $firstBlank = $blanksValid[0];
                $envelope->setFirstNum($firstBlank->getNumber());
                $envelope->setLeadingZeros($firstBlank->getLeadingZeros());
                $envelope->setLegalEntity($firstBlank->getLegalEntity());
                if ($referenceType->getIsSerie()) {
                    $envelope->setSerie($serie);
                }

                $envelope->setAmount(count($blanksValid));
                $envelope->setReferenceType($referenceType);
                $this->em->persist($envelope);
                $this->em->flush();

                $cnt = 0;
                foreach ($blanksValid as &$blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                    $oldStatus = $blank->getStatus();
                    $blank->setReferenceman($referenceman);
                    $blank->setOperatorReferencemanEnvelope($envelope);
                    $blank->setStatus('appointedToReferencemanFromOperator');
                    $blank->setReferencemanApplied(null);
                    $this->em->persist($blank);

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
                    $lifeLog->setOperationStatus($lifeLog::OR_REVERT_BLANK_TO_REFER);
                    $lifeLog->setWorkplace($this->workplace);
                    $lifeLog->setEnvelopeId($envelope->getId());
                    $lifeLog->setEnvelopeType('blank_operator_referenceman_envelope');

                    $lifeLog->setStartStatus($oldStatus);
                    $lifeLog->setEndStatus($blank->getStatus());

                    $lifeLog->setStartUser($this->getUserOrSuccessor());
                    $lifeLog->setEndUser($referenceman);

                    $this->em->persist($lifeLog);

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

                $this->addFlash('success', 'Передали');

                return $this->redirectToRoute('operator_blanks_envelopes_of_transfered_blanks_to_referenceman');
            }
        }

        $referenceTypes = $this->em->getRepository('CommonBundle:ReferenceType')->createQueryBuilder('rt')
            ->addSelect('rt')->getQuery()->getArrayResult();

        $referenceTypesIdKeys = [];
        foreach ($referenceTypes as $referenceType) {
            $referenceTypesIdKeys[$referenceType['id']] = $referenceType;
        }

        $referenceTypeSeries = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('(b.serie) as serie, (b.reference_type) as reference_type, (b.legal_entity) as legal_entity')
            ->groupBy('serie')
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByOperator')
            ->getQuery()->getResult();

        $blanksFilter = [];
        foreach ($referenceTypeSeries as $blank) {
            $blanksFilter[$blank['legal_entity']][$blank['reference_type']][] = (string)$blank['serie'];
        }

        $referenceTypes = $this->em->getRepository('CommonBundle:ReferenceType')->createQueryBuilder('rt')
            ->addSelect('rt')->getQuery()->getArrayResult();

        $referenceTypesIdKeys = [];
        foreach ($referenceTypes as $referenceType) {
            $referenceTypesIdKeys[$referenceType['id']] = $referenceType;
        }

        return $this->render('AppBundle:Operator:transfer_blanks_to_referenceman.html.twig', [
            'form'                    => $form->createView(),
            'form2'                   => $form2->createView(),
            'blanks'                  => $blanks,
            'blanksFilter'            => $blanksFilter,
            'reference_types'         => $referenceTypesIdKeys,
            'serie_selected'          => $form->get('serie')->getData(),
            'reference_type_selected' => $form->get('reference_type')->getViewData(),
        ]);
    }

    /**
    * @Route("/replacement-blanks-with-no-stamps/",
    * name="operator_blanks__replacement_blanks_with_no_stamps")
    */
    public function replacementBlanksWithNoStamps(Request $request)
    {
        $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT b.serie')
            ->andWhere('b.status = :status')->setParameter('status', 'usedByOperator')
            ->andWhere('b.stamp = :stamp')->setParameter('stamp', false)
            ->orderBy('b.serie')
            ->getQuery()->getArrayResult();

        $seriesChoices = [];
        foreach ($series as $serie) {
            if ($serie['serie'] != '') {
                $seriesChoices[$serie['serie']] = $serie['serie'];
            }
        }

        $fb = $this->createFormBuilder([], [
            'translation_domain' => false,
        ]);
        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'placeholder'       => ' - Выберите - ',
            'choices'           => $seriesChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);
        $fb->add('number', TextType::class, [
            'label'       => 'Номер бланка',
            'attr'        => ['bsize' => 9],
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('date_giving', DateType::class, [
            'label'       => 'Дата выдачи',
            'placeholder' => '--',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('find', SubmitType::class, [
            'label' => 'Найти',
            'attr'  => ['class' => 'btn btn-success'],
        ]);
        $form = $fb->getForm();
        $form->handleRequest($request);
        $serviceLogs = [];

        if ($form->isValid()) {
            $serviceLog = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
                ->leftJoin('sl.blank', 'b')->addSelect('b')
                ->leftJoin('sl.service', 's')->addSelect('s')
                ->leftJoin('s.agreements', 'a')->addSelect('a')
                ->andWhere('a.workplace = :workplace')->setParameter('workplace', $this->workplace)
                ->andWhere('b.status = :status')->setParameter('status', 'usedByOperator')
                ->andWhere('b.stamp = :stamp')->setParameter('stamp', false)
                ->andWhere('b.serie = :serie')->setParameter('serie', $form->get('serie')->getData())
                ->andWhere('b.number = :number')->setParameter('number', $form->get('number')->getData())
                ->andWhere('b.leading_zeros = :leading_zeros')
                ->setParameter('leading_zeros', strlen($form->get('number')->getData()))
                ->andWhere('sl.date_giving = :date_giving')
                ->setParameter('date_giving', $form->get('date_giving')->getData())
                ->getQuery()->getOneOrNullResult();

            return $this->render('AppBundle:Operator/Replacement:founded.html.twig', [
                'serviceLog' => $serviceLog,
            ]);
        }

        return $this->render('AppBundle:Operator/Replacement:search.html.twig', [
            'form'        => $form->createView(),
            'serviceLogs' => $serviceLogs,
        ]);
    }

    /**
     * @Route("/replacement-blanks-with-no-stamps/sl-{id}/upload-docs/",
     * name="operator_blanks__replacement_blank_with_no_stamps__upload")
     */
    public function replacementBlankWithNoStampsUpload($id)
    {
        /** @var $serviceLog \KreaLab\CommonBundle\Entity\ServiceLog */
        $serviceLog = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
            ->leftJoin('sl.blank', 'b')->addSelect('b')
            ->leftJoin('sl.service', 's')->addSelect('s')
            ->leftJoin('s.agreements', 'a')->addSelect('a')
            ->andWhere('a.workplace = :workplace')->setParameter('workplace', $this->workplace)
            ->andWhere('b.status = :status')->setParameter('status', 'usedByOperator')
            ->andWhere('b.stamp = :stamp')->setParameter('stamp', false)
            ->andWhere('sl.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
        if (!$serviceLog) {
            throw $this->createNotFoundException('no service log');
        }

        /** @var $agreement \KreaLab\CommonBundle\Entity\Agreement */
        $agreement = $serviceLog->getService()->getAgreementByWorkplace($this->workplace);
        if (!$agreement) {
            throw $this->createNotFoundException('no agreement');
        }

        $params = $serviceLog->getParams();

        $params['narco_docs']  = isset($params['narco_docs']) ? $params['narco_docs'] : [];
        $params['psycho_docs'] = isset($params['psycho_docs']) ? $params['psycho_docs'] : [];

        $narcoDocs = $this->em->getRepository('CommonBundle:Image')->createQueryBuilder('i')
            ->andWhere('i.id IN (:ids)')->setParameter('ids', $params['narco_docs'])
            ->getQuery()->execute();

        $psychoDocs = $this->em->getRepository('CommonBundle:Image')->createQueryBuilder('i')
            ->andWhere('i.id IN (:ids)')->setParameter('ids', $params['psycho_docs'])
            ->getQuery()->execute();

        return $this->render('AppBundle:Operator/Replacement:upload.html.twig', [
            'serviceLog' => $serviceLog,
            'narcoDocs'  => $narcoDocs,
            'psychoDocs' => $psychoDocs,
        ]);
    }

    /**
     * @Route("/replacement-blanks-with-no-stamps/sl-{id}/upload-docs/replace/",
     * name="operator_blanks__replacement_blank_with_no_stamps__replace") */
    public function replacementBlankWithNoStampsReplace(Request $request, $id)
    {
        /** @var $serviceLog \KreaLab\CommonBundle\Entity\ServiceLog */
        $serviceLog = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
            ->leftJoin('sl.service', 's')->addSelect('s')
            ->leftJoin('s.agreements', 'a')->addSelect('a')
            ->andWhere('a.workplace = :workplace')->setParameter('workplace', $this->workplace)
            ->leftJoin('sl.blank', 'b')->addSelect('b')
            ->andWhere('b.status = :status')->setParameter('status', 'usedByOperator')
            ->andWhere('b.stamp = :stamp')->setParameter('stamp', false)
            ->andWhere('sl.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
        if (!$serviceLog) {
            throw $this->createNotFoundException();
        }

        /** @var $agreement \KreaLab\CommonBundle\Entity\Agreement */
        $agreement = $this->em->getRepository('CommonBundle:Agreement')->createQueryBuilder('a')
            ->andWhere('a.service = :serviceId')->setParameter('serviceId', $serviceLog->getService())
            ->andWhere('a.workplace = :workplaceId')->setParameter('workplaceId', $this->workplace)
            ->andWhere('a.type IS NOT NULL')
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$agreement) {
            throw $this->createNotFoundException();
        }

        $referenceType = $serviceLog->getBlank()->getReferenceType();

        $executorOrGuarantor = $agreement->getExecutorOrGuarantor();

        $seriesChoices = $this->em->getRepository('CommonBundle:Blank')
            ->getCurrentSeries($this->getUserOrSuccessor(), true, $executorOrGuarantor, $referenceType);

        $numberChoices = [];
        if (!empty($request->get('form')['number'])) {
            $numberChoices[$request->get('form')['number']] = $request->get('form')['number'];
        }

        $fb = $this->createFormBuilder([], [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'placeholder'       => ' - Выберите серию - ',
            'choices'           => $seriesChoices,
            'constraints'       => new Assert\NotBlank(),
            'choices_as_values' => true,
        ]);
        $fb->add('number', ChoiceType::class, [
            'constraints'       => new Assert\NotBlank(),
            'choices'           => $numberChoices,
            'label'             => 'Номер бланка',
            'attr'              => ['bsize' => 9],
            'choices_as_values' => true,
        ]);
        $fb->add('date_giving', DateType::class, [
            'label'       => 'Дата выдачи справки',
            'data'        => new \DateTime(),
            'widget'      => 'single_text',
            'format'      => 'dd.MM.yyyy',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('save', SubmitType::class, [
            'label' => 'Сохранить',
            'attr'  => ['class' => 'btn-success btn-lg'],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $blank = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUser())
                ->andWhere('b.status = :status')->setParameter('status', 'acceptedByOperator')
                ->andWhere('b.legal_entity = :legal_entity')->setParameter('legal_entity', $executorOrGuarantor)
                ->andWhere('b.stamp = :stamp')->setParameter('stamp', true)
                ->andWhere('b.number = :number')->setParameter('number', $form->get('number')->getData())
                ->andWhere('b.serie = :serie')->setParameter('serie', $form->get('serie')->getData())
                ->getQuery()->getOneOrNullResult();
            if (!$blank) {
                throw $this->createNotFoundException();
            }

            $oldBlank = $serviceLog->getBlank();
            $oldBlank->setStatus('replacedBecauseNoStampByOperator');
            $oldBlank->setReplacedByBlankWithStamp($blank);
            $this->em->persist($oldBlank);
            $this->em->flush();

            $params              = $serviceLog->getParams();
            $params['serie']     = $blank->getSerie();
            $params['num_blank'] = $blank->getNumber();

            $newServiceLog = new ServiceLog();
            $newServiceLog->setParams($params);
            $newServiceLog->setNumBlank($blank->getNumber());
            $newServiceLog->setDateGiving($form->get('date_giving')->getData());
            $newServiceLog->setSum(0);
            $newServiceLog->setBlank($blank);
            $newServiceLog->setOperator($serviceLog->getOperator());
            $newServiceLog->setParams($serviceLog->getParams());
            $newServiceLog->setNum($serviceLog->getNum());
            $newServiceLog->setDateGiving($serviceLog->getDateGiving());
            $newServiceLog->setService($serviceLog->getService());
            $newServiceLog->setWorkplace($serviceLog->getWorkplace());
            $newServiceLog->setFirstName($serviceLog->getFirstName());
            $newServiceLog->setLastName($serviceLog->getLastName());
            $newServiceLog->setMedicalCenterError($serviceLog->getMedicalCenterError());
            $newServiceLog->setPatronymic($serviceLog->getPatronymic());
            $newServiceLog->setParent($serviceLog->getParent());
            $newServiceLog->setBirthday($serviceLog->getBirthday());
            $newServiceLog->setImport($serviceLog->getImport());
            $newServiceLog->setEegConclusion($serviceLog->getEegConclusion());
            $this->em->persist($newServiceLog);
            $this->em->flush();

            $blank->setReplacedBlankWithNoStamp($oldBlank);
            $blank->setServiceLog($newServiceLog);
            $blank->setStatus('usedByOperator');
            $blank->setServiceLogApplied(new \DateTime());
            $this->em->persist($blank);
            $this->em->flush();

            $this->addFlash('success', 'Поменяли');

            return $this->redirectToRoute('operator_blanks__replacement_blanks_with_no_stamps');
        }

        $intervals = $this->em->getRepository('CommonBundle:Blank')
            ->getCurrentIntervalsFlatten($this->getUserOrSuccessor(), true, $executorOrGuarantor, $referenceType);

        $intervals = isset($intervals[$executorOrGuarantor->getId()][$referenceType->getId()])
            ? $intervals[$executorOrGuarantor->getId()][$referenceType->getId()] : [];

        return $this->render('AppBundle:Operator/Replacement:replace.html.twig', [
            'form'           => $form->createView(),
            'reference_type' => $referenceType,
            'serviceLog'     => $serviceLog,
            'info'           => ['intervals' => $intervals],
        ]);
    }

    /**
     * @Route("/service-logs/{id}/upload-{type}-docs/", name="service_log_upload_docs")
     */
    public function serviceLogUploadDocsAction(Request $request, $id, $type)
    {
        $result = ['files' => []];
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $serviceLog = $this->em->find('CommonBundle:ServiceLog', $id);
        if (!$serviceLog) {
            throw $this->createNotFoundException();
        }

        $params        = $serviceLog->getParams();
        $params[$type] = isset($params[$type]) ? $params[$type] : [];

        $licm = $this->container->get('liip_imagine.cache.manager');

        foreach ($request->files as $files) {
            foreach ($files as $file) {
                /** @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
                if ($file->getClientSize() > 5 * 1024 * 1024) {
                    $result = ['errors' => ['Слишком большой размер файла.']];
                } elseif (!in_array($file->getMimeType(), ['image/jpg', 'image/jpeg', 'image/png'])) {
                    $result = ['errors' => ['Неразрешённый формат файла.']];
                } else {
                    $doc = new Image();
                    $doc->setUploadFile($file);
                    $this->em->persist($doc);
                    $this->em->flush();

                    $params[$type][] = $doc->getId();

                    $result['files'][] = [
                        'id'          => $doc->getId(),
                        'webPath'     => $licm->getBrowserPath($doc->getWebPath(), 'doc'),
                        'webPathOrig' => $doc->getWebPath(),
                    ];
                }
            }
        }

        $serviceLog->setParams($params);
        $this->em->persist($serviceLog);
        $this->em->flush();

        return new JsonResponse($result);
    }

    /**
     * @Route("/service-logs/{id}/delete-{type}-docs/", name="service_log_delete_docs")
     */
    public function serviceLogDeleteDocsAction(Request $request, $id, $type)
    {
        $result = [];

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $serviceLog = $this->em->find('CommonBundle:ServiceLog', $id);
        if (!$serviceLog) {
            throw $this->createNotFoundException();
        }

        $params        = $serviceLog->getParams();
        $params[$type] = isset($params[$type]) ? $params[$type] : [];

        if ($request->isMethod('post')) {
            $docId = $request->get('doc_id');
            $key   = array_search($docId, $params[$type]);
            if ($key !== false) {
                unset($params[$type][$key]);
            }

            $serviceLog->setParams($params);
            $this->em->persist($serviceLog);
            $this->em->flush();
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/logout/", name="operator_logout")
     */
    public function logout()
    {
        $filials = $this->getUserOrSuccessor()->getFilials();
        $filial  = $filials[0];

        $shiftLog = $this->em->getRepository('CommonBundle:ShiftLog')->createQueryBuilder('s')
            ->andWhere('s.user = :user')->setParameter('user', $this->getUserOrSuccessor())
            ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
            ->andWhere('s.date = :date')->setParameter('date', new \DateTime('today'))
            ->andWhere('s.endTime IS NULL')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if ($shiftLog) {
            $shiftLog->setEndTime(new \DateTime());
            $this->em->persist($shiftLog);
            $this->em->flush();
        }

        return $this->redirectToRoute('logout');
    }

    /** @Route("/money-return/found-{serviceLog}/", name="operator_blanks__return_money__found") */
    public function moneyReturnFoundAction(Request $request, $serviceLog)
    {
        /** @var $serviceLog \KreaLab\CommonBundle\Entity\ServiceLog */
        $serviceLog = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
            ->leftJoin('sl.blank', 'b')->addSelect('b')
            ->andWhere('b.status = :status')->setParameter('status', 'usedByOperator')
            ->andWhere('sl.id = :id')->setParameter('id', $serviceLog)
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();

        if (!$serviceLog) {
            throw $this->createNotFoundException('No service log');
        }

        $fb = $this->createFormBuilder(null, [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('return_comment', TextareaType::class, [
            'required' => false,
            'label'    => 'Комментарий',
        ]);

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($request->isMethod('post')) {
            if ($form->isValid()) { /** @var $oldBlank \KreaLab\CommonBundle\Entity\Blank */
                $oldBlank = $serviceLog->getBlank();
                $oldBlank->setStatus('cancelledByOperator');
                $oldBlank->setReplacedByBlankWithStamp($oldBlank);
                $this->em->persist($oldBlank);
                $this->em->flush();

                $params              = $serviceLog->getParams();
                $params['serie']     = $oldBlank->getSerie();
                $params['num_blank'] = $oldBlank->getNumber();

                $newServiceLog = new ServiceLog();
                $newServiceLog->setParams($params);
                $newServiceLog->setNumBlank($oldBlank->getNumber());
                $newServiceLog->setDateGiving($serviceLog->getDateGiving());

                /** @var $oldOldBlank \KreaLab\CommonBundle\Entity\Blank */
                /** @var $oldOldServiceLog \KreaLab\CommonBundle\Entity\ServiceLog */

                if ($oldBlank->getStatus() == 'replacedBecauseNoStampByOperator') {
                    $oldOldBlank->getReplacedBlankWithNoStamp();
                    $oldOldServiceLog = $oldOldBlank->getServiceLog();
                    $newServiceLog->setSum(-$oldOldServiceLog->getSum());
                } else {
                    $newServiceLog->setSum(-$serviceLog->getSum());
                }

                $newServiceLog->setBlank($oldBlank);
                $newServiceLog->setOperator($this->getUserOrSuccessor());
                $newServiceLog->setParams($serviceLog->getParams());
                $newServiceLog->setNum($serviceLog->getNum());
                $newServiceLog->setDateGiving($serviceLog->getDateGiving());
                $newServiceLog->setService($serviceLog->getService());
                $newServiceLog->setWorkplace($serviceLog->getWorkplace());
                $newServiceLog->setFirstName($serviceLog->getFirstName());
                $newServiceLog->setLastName($serviceLog->getLastName());
                $newServiceLog->setMedicalCenterError($serviceLog->getMedicalCenterError());
                $newServiceLog->setPatronymic($serviceLog->getPatronymic());
                $newServiceLog->setParent($serviceLog->getParent());
                $newServiceLog->setBirthday($serviceLog->getBirthday());
                $newServiceLog->setImport($serviceLog->getImport());
                $newServiceLog->setEegConclusion($serviceLog->getEegConclusion());
                $newServiceLog->setReturnComment($form->get('return_comment')->getData());
                $this->em->persist($newServiceLog);
                $this->em->flush();

                $this->addFlash('success', 'Вернули');
                return $this->redirectToRoute('operator_blanks__return_money__search');
            }
        }

        return $this->render('AppBundle:Operator/ReturnMoney:found.html.twig', [
            'form'       => $form->createView(),
            'serviceLog' => $serviceLog,
        ]);
    }

    /** @Route("/money-return/", name="operator_blanks__return_money__search") */
    public function moneyReturnSearchAction(Request $request)
    {
        $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT b.serie')
            ->andWhere('b.status = :status')->setParameter('status', 'usedByOperator')
            ->andWhere('b.legal_entity = :legal_entity')
            ->setParameter('legal_entity', $this->workplace->getLegalEntity())
            ->orderBy('b.serie')
            ->getQuery()->getArrayResult();

        $seriesChoices = [];
        foreach ($series as $serie) {
            if ($serie['serie'] != '') {
                $seriesChoices[$serie['serie']] = $serie['serie'];
            }
        }

        $fb = $this->createFormBuilder([], [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('reference_type', EntityType::class, [
            'label'         => 'Тип бланка',
            'class'         => 'CommonBundle:ReferenceType',
            'placeholder'   => ' - Выберите тип бланка - ',
            'constraints'   => new Assert\NotBlank(),
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('rt')
                    ->innerJoin('rt.blanks', 'b', 'WITH', 'b.status = :status')
                    ->setParameter('status', 'usedByOperator')
                    ->andWhere('b.operator = :operator')->setParameter('operator', $this->getUser())
                    ;
            },
        ]);

        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'placeholder'       => ' - Выберите - ',
            'choices'           => $seriesChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);
        $fb->add('number', TextType::class, [
            'label'       => 'Номер бланка',
            'attr'        => ['bsize' => 9],
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('date_giving', DateType::class, [
            'label'       => 'Дата выдачи',
            'placeholder' => '--',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('find', SubmitType::class, [
            'label' => 'Найти',
            'attr'  => ['class' => 'btn-success btn-lg'],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $qb = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
                ->leftJoin('sl.blank', 'b')->addSelect('b')
                ->leftJoin('sl.service', 's')->addSelect('s')
                ->leftJoin('s.agreements', 'a')->addSelect('a')
                ->andWhere('a.workplace = :workplace')->setParameter('workplace', $this->workplace)
                ->andWhere('b.number = :number')->setParameter('number', (int)$form->get('number')->getData())
                ->andWhere('sl.date_giving = :date_giving')
                ->andWhere('sl.medical_center_error is NULL')
                ->setParameter('date_giving', $form->get('date_giving')->getData())
                ->andWhere('b.status = :status')->setParameter('status', 'usedByOperator')
            ;

            $referenceType = $this->em->getRepository('CommonBundle:ReferenceType')
                ->find($form->get('reference_type')->getData());

            if ($referenceType->getIsSerie()) {
                $qb->andWhere('b.serie = :serie')->setParameter('serie', $form->get('serie')->getData());
            }

            $serviceLog = $qb->getQuery()->getOneOrNullResult();

            if ($serviceLog) {
                return $this->redirectToRoute('operator_blanks__return_money__found', [
                    'serviceLog' => $serviceLog->getId(),
                ]);
            } else {
                $this->addFlash('danger', 'Не найдено');
            }
        }

        $referenceTypes = $this->em->getRepository('CommonBundle:ReferenceType')->createQueryBuilder('rt')
            ->getQuery()->getArrayResult();

        $referenceTypesIdKeys = [];
        foreach ($referenceTypes as $referenceType) {
            $referenceTypesIdKeys[$referenceType['id']] = $referenceType;
        }

        return $this->render('AppBundle:Operator/ReturnMoney:search.html.twig', [
            'form'            => $form->createView(),
            'reference_types' => $referenceTypesIdKeys,
        ]);
    }
}
