<?php

namespace KreaLab\AdminBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Doctrine\ORM\QueryBuilder;
use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;
use KreaLab\CommonBundle\Entity\OperatorSchedule;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class UserController extends AbstractEntityController
{
    protected $listFields = [
        ['id', 'min_col'],
        'full_name',
        ['username', 'min_col text-left'],
        ['roles', 'min_col text-left'],
    ];
    protected $orderBy    = [
        'active'     => 'DESC',
        'last_name'  => 'ASC',
        'first_name' => 'ASC',
        'patronymic' => 'ASC',
    ];
    protected $perms      = ['ROLE_MANAGE_WORKERS'];
    protected $tmplItem   = 'AdminBundle:User:item.html.twig';
    protected $tmplList   = 'AdminBundle:User:list.html.twig';
    protected $withFilter = true;

    protected function preForm($entity)
    {
        /** @var $entity \KreaLab\CommonBundle\Entity\User */

        if ($entity == $this->getUser()) {
            throw $this->createAccessDeniedException('You can\'t edit yourself');
        }

        if ($entity && !$entity->isEditable($this->getUser())) {
            throw $this->createAccessDeniedException();
        }

        return $entity;
    }

    protected function getFormOptions()
    {
        return [
            'translation_domain' => $this->entityNameS,
            'encoder'            => $this->get('security.password_encoder'),
            'user'               => $this->getUser(),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getRenderExtraOptions($entity)
    {
        $filials    = $this->em->getRepository('CommonBundle:Filial')->createQueryBuilder('f')
            ->andWhere('f.active = :factive')->setParameter('factive', true)
            ->leftJoin('f.workplaces', 'w')->addSelect('w')
            ->andWhere('w.active = :wactive')->setParameter('wactive', true)
            ->getQuery()->execute();
        $workplaces = [];
        foreach ($filials as $filial) {
            /** @var $filial \KreaLab\CommonBundle\Entity\Filial */
            $workplaces[$filial->getId()] = [];
            foreach ($filial->getWorkplaces() as $workplace) {
                $workplaces[$filial->getId()][] = $workplace->getId();
            }
        }

        return ['workplaces' => $workplaces];
    }

    protected function filterFields(FormBuilderInterface $fb)
    {
        $roleChoices = [
            'ROLE_SUPERADMIN'      => 'ROLE_SUPERADMIN',
            'ROLE_ADMIN'           => 'ROLE_ADMIN',
            'ROLE_ARCHIVARIUS'     => 'ROLE_ARCHIVARIUS',
            'ROLE_MANAGE_FILIALS'  => 'ROLE_MANAGE_FILIALS',
            'ROLE_MANAGE_WORKERS'  => 'ROLE_MANAGE_WORKERS',
            'ROLE_CASHIER'         => 'ROLE_CASHIER',
            'ROLE_SUPERVISOR'      => 'ROLE_SUPERVISOR',
            'ROLE_COURIER'         => 'ROLE_COURIER',
            'ROLE_TREASURER'       => 'ROLE_TREASURER',
            'ROLE_ORDERMAN'        => 'ROLE_ORDERMAN',
            'ROLE_ACQUITTANCEMAN'  => 'ROLE_ACQUITTANCEMAN',
            'ROLE_STOCKMAN'        => 'ROLE_STOCKMAN',
            'ROLE_REFERENCEMAN'    => 'ROLE_REFERENCEMAN',
            'ROLE_SENIOR_OPERATOR' => 'ROLE_SENIOR_OPERATOR',
            'ROLE_OPERATOR'        => 'ROLE_OPERATOR',
            'ROLE_REPLACER'        => 'ROLE_REPLACER',
        ];

        $fb
            ->add('username', TextType::class, [
                'required' => false,
            ])
            ->add('last_name', TextType::class, [
                'required' => false,
            ])
            ->add('first_name', TextType::class, [
                'required' => false,
            ])
            ->add('patronymic', TextType::class, [
                'required' => false,
            ])
            ->add('role', ChoiceType::class, [
                'choices'           => $roleChoices,
                'required'          => false,
                'expanded'          => true,
                'multiple'          => true,
                'choices_as_values' => true,
            ]);
    }

    protected function filterQb(Form $filter_form, QueryBuilder $qb)
    {
        $data = $filter_form->get('username')->getData();
        if (!empty($data)) {
            $qb->andWhere('e.username LIKE :username')->setParameter(':username', '%'.$data.'%');
        }

        $data = $filter_form->get('last_name')->getData();
        if (!empty($data)) {
            $qb->andWhere('e.last_name LIKE :last_name')->setParameter(':last_name', '%'.$data.'%');
        }

        $data = $filter_form->get('first_name')->getData();
        if (!empty($data)) {
            $qb->andWhere('e.first_name LIKE :first_name')->setParameter(':first_name', '%'.$data.'%');
        }

        $data = $filter_form->get('patronymic')->getData();
        if (!empty($data)) {
            $qb->andWhere('e.patronymic LIKE :patronymic')->setParameter(':patronymic', '%'.$data.'%');
        }

        $data = $filter_form->get('role')->getData();
        if (!empty($data)) {
            $qb->andWhere('e.roles IN (:roles)')->setParameter('roles', $data);
        }
    }

    public function scheduleAction(Request $request, $id)
    {
        /** @var $operator \KreaLab\CommonBundle\Entity\User */
        $operator = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
            ->leftJoin('u.filials', 'f')->addSelect('f')
            ->andWhere('u.roles LIKE :role_operator')
            ->setParameter('role_operator', '%ROLE_OPERATOR%')
            ->andWhere('u.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
        if (!$operator) {
            throw $this->createNotFoundException('no operator');
        }

        $filialId = (int)$request->get('filial_id');
        $filial   = null;
        if ($operator->hasOnlyRole('ROLE_OPERATOR')) {
            if ($operator->getWorkplace()) {
                $filial = $operator->getWorkplace()->getFilial();
            }
        } elseif ($operator->hasOneOfRoles(['ROLE_SENIOR_OPERATOR', 'ROLE_COURIER'])) {
            foreach ($operator->getFilials() as $oFilial) { /** @var $oFilial \KreaLab\CommonBundle\Entity\Filial */
                if ($oFilial->getId() == $filialId) {
                    $filial = $oFilial;
                }
            }
        } else {
            $filial = $this->em->getRepository('CommonBundle:Filial')->findOneBy([
                'active' => true,
                'id'     => $filialId,
            ]);
        }

        if (!$filial) {
            return $this->redirectToRoute('admin_user_select_filial', ['id' => $operator->getId()]);
        }

        $startDisabled = new \DateTime('yesterday');
        $endDisabled   = new \DateTime('today + 1 year');

        $datesDisabled = [];
        for (; $startDisabled <= $endDisabled; $startDisabled->add(new \DateInterval('P1D'))) {
            $datesDisabled[$startDisabled->format('Y-m-d')] = $startDisabled->format('Y-m-d');
        }

        $startDate = new \DateTime('yesterday');

        $filialSchedules = $this->em->getRepository('CommonBundle:Schedule')->createQueryBuilder('s')
            ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
            ->andWhere('s.date >= :date')->setParameter('date', $startDate)
            ->getQuery()->getResult();

        $filialDates = [];
        foreach ($filialSchedules as $schedule) { /** @var $schedule \KreaLab\CommonBundle\Entity\Schedule */
            $filialDates[$schedule->getDate()->format('Y-m-d')] = $schedule->getDate()->format('Y-m-d');
            unset($datesDisabled[$schedule->getDate()->format('Y-m-d')]);
        }

        $startDate = new \DateTime('yesterday');

        $schedules = $this->em->getRepository('CommonBundle:OperatorSchedule')->createQueryBuilder('os')
            ->andWhere('os.operator = :operator')->setParameter('operator', $operator)
            ->andWhere('os.date >= :date')->setParameter('date', $startDate)
            ->andWhere('os.date IN (:dates)')->setParameter('dates', $filialDates)
            ->getQuery()->getResult();

        $dates = [];
        foreach ($schedules as $schedule) { /** @var $schedule \KreaLab\CommonBundle\Entity\OperatorSchedule */
            $dates[$schedule->getDate()->format('Y-m-d')] = $schedule->getDate()->format('Y-m-d');
        }

        $startDate->add(new \DateInterval('P1D'));
        $endDate = new \DateTime('today + 1 year');

        return $this->render('AdminBundle:User:schedule.html.twig', [
            'operator'      => $operator,
            'startDate'     => $startDate->format('d.m.Y'),
            'endDate'       => $endDate->format('d.m.Y'),
            'datesDisabled' => $datesDisabled,
            'dates'         => $dates,
            'filial'        => $filial,
        ]);
    }

    public function scheduleToggleAction(Request $request, $id)
    {
        $date  = $request->get('date');
        $date  = new \DateTime($date);
        $today = new \DateTime('today');

        if ($date < $today) {
            return new JsonResponse(['error' => 'date < today', 500]);
        }

        $operator = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role_operator')->setParameter('role_operator', '%ROLE_OPERATOR%')
            ->andWhere('u.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        if (!$operator) {
            return new JsonResponse(['error' => 'no operator', 500]);
        }

        $filialId = $request->get('filial_id');

        if ($filialId) {
            $filial = $this->em->getRepository('CommonBundle:Filial')->findOneBy([
                'id'     => $filialId,
                'active' => true,
            ]);
        } else {
            $filial = $operator->getWorkplace()->getFilial();
        }

        if (!$filial) {
            return new JsonResponse(['error' => 'no filials', 500]);
        }

        $startDate = new \DateTime('today');

        $filialSchedules = $this->em->getRepository('CommonBundle:Schedule')->createQueryBuilder('s')
            ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
            ->andWhere('s.date >= :date')->setParameter('date', $startDate)
            ->getQuery()->getResult();

        $filialDates = [];
        foreach ($filialSchedules as $schedule) { /** @var $schedule \KreaLab\CommonBundle\Entity\Schedule */
            $filialDates[$schedule->getDate()->format('Y-m-d')] = $schedule->getDate()->format('Y-m-d');
        }

        $schedule = $this->em->getRepository('CommonBundle:OperatorSchedule')->createQueryBuilder('os')
            ->andWhere('os.date = :date')->setParameter('date', $date)
            ->andWhere('os.operator = :operator')->setParameter('operator', $operator)
            ->andWhere('os.date IN (:dates)')->setParameter('dates', $filialDates)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if ($schedule) {
            $this->em->remove($schedule);
            $this->em->flush();

            $answer = [
                'date'    => $date->format('Y-m-d'),
                'deleted' => true,
            ];
        } else {
            $schedule = new OperatorSchedule();
            $schedule->setDate($date);
            $schedule->setOperator($operator);
            $this->em->persist($schedule);
            $this->em->flush();

            $answer = [
                'date'    => $date->format('Y-m-d'),
                'created' => true,
            ];
        }

        $schedules = $this->em->getRepository('CommonBundle:OperatorSchedule')->createQueryBuilder('os')
            ->andWhere('os.operator = :operator')->setParameter('operator', $operator)
            ->getQuery()->getResult();

        $dates = [];
        foreach ($schedules as $schedule) { /** @var $schedule \KreaLab\CommonBundle\Entity\OperatorSchedule */
            $dates[$schedule->getDate()->format('Y-m-d')] = $schedule->getDate()->format('Y-m-d');
        }

        $answer['dates'] = $dates;

        return new JsonResponse($answer);
    }

    public function scheduleFillWeekAction(Request $request, $id)
    {
        $filialId = $request->get('filial_id');

        /** @var $operator \KreaLab\CommonBundle\Entity\User */
        $operator = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
            ->leftJoin('u.filials', 'f')->addSelect('f')
            ->andWhere('u.roles LIKE :role_operator')->setParameter('role_operator', '%ROLE_OPERATOR%')
            ->andWhere('u.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        if (!$operator) {
            throw $this->createNotFoundException('no operator');
        }

        if ($filialId) {
            $filial = $this->em->getRepository('CommonBundle:Filial')->find($filialId);
        } else {
            $filial = $operator->getWorkplace()->getFilial();
        }


        if (!$filial) {
            throw $this->createNotFoundException('no filial');
        }

        $fb = $this->createFormBuilder([], [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);

        $fb->add('day', ChoiceType::class, [
            'label'             => 'День недели',
            'required'          => false,
            'choices_as_values' => true,
            'multiple'          => true,
            'expanded'          => true,
            'choices'           => array_flip([
                'Monday'    => 'Понедельник',
                'Tuesday'   => 'Вторник',
                'Wednesday' => 'Среда',
                'Thursday'  => 'Четверг',
                'Friday'    => 'Пятница',
                'Saturday'  => 'Суббота',
                'Sunday'    => 'Воскресенье',
            ]),
        ]);

        $fb->add('fill_from', DateType::class, [
            'label'       => 'Заполнить с',
            'input'       => 'datetime',
            'widget'      => 'choice',
            'data'        => new \DateTime('today'),
            'constraints' => new Assert\GreaterThanOrEqual(new \DateTime('today')),
        ]);
        $fb->add('fill_to', DateType::class, [
            'label'       => 'Заполнить по',
            'input'       => 'datetime',
            'widget'      => 'choice',
            'data'        => new \DateTime('today + 1 days'),
            'constraints' => new Assert\GreaterThanOrEqual(new \DateTime('today + 1 days')),
        ]);

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $from = $form->get('fill_from')->getData(); /** var $from \DateTime */
            $to   = $form->get('fill_to')->getData(); /** var $to \DateTime */

            for (; $from <= $to; $from->add(new \DateInterval('P1D'))) {
                if (in_array($from->format('l'), $form->get('day')->getData())) {
                    $filialSchedule = $this->em->getRepository('CommonBundle:Schedule')->createQueryBuilder('s')
                        ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
                        ->andWhere('s.date = :date')->setParameter('date', $from)
                        ->getQuery()->getOneOrNullResult();

                    if ($filialSchedule) {
                        $operatorSchedule = $this->em->getRepository('CommonBundle:OperatorSchedule')->findOneBy([
                            'date'     => $from,
                            'operator' => $operator,
                        ]);

                        if (!$operatorSchedule) {
                            $operatorSchedule = new OperatorSchedule();
                            $operatorSchedule->setDate($from);
                            $operatorSchedule->setOperator($operator);
                            $this->em->persist($operatorSchedule);
                            $this->em->flush();
                        }
                    }
                }
            }

            $this->addFlash('success', 'Заполнили');
            return $this->redirectToRoute('admin_user_schedule', ['id' => $operator->getId()]);
        }


        return $this->render('AdminBundle:User:schedule_fill_week.html.twig', [
            'form'     => $form->createView(),
            'filial'   => $filial,
            'operator' => $operator,
        ]);
    }

    public function selectFilialAction(Request $request, $id)
    {
        /** @var $operator \KreaLab\CommonBundle\Entity\User */
        $operator = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
            ->leftJoin('u.filials', 'f')->addSelect('f')
            ->andWhere('u.roles LIKE :role_operator')
            ->setParameter('role_operator', '%ROLE_OPERATOR%')
            ->andWhere('u.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
        if (!$operator) {
            throw $this->createNotFoundException('no operator');
        }

        $filialChoices = [];
        if ($operator->hasOnlyRole('ROLE_OPERATOR')) {
            if ($operator->getWorkplace()) {
                $filial                            = $operator->getWorkplace()->getFilial();
                $filialChoices[$filial->getName()] = $filial->getId();
            }
        } elseif ($operator->hasOneOfRoles(['ROLE_SENIOR_OPERATOR', 'ROLE_COURIER'])) {
            foreach ($operator->getFilials() as $filial) { /** @var $filial \KreaLab\CommonBundle\Entity\Filial */
                $filialChoices[$filial->getName()] = $filial->getId();
            }
        } else {
            $filials = $this->em->getRepository('CommonBundle:Filial')->findOneBy([
                'active' => true,
            ]);

            foreach ($filials as $filial) {  /** @var $filial \KreaLab\CommonBundle\Entity\Filial */
                $filialChoices[$filial->getName()] = $filial->getId();
            }
        }

        if (count($filialChoices) == 1) {
            return $this->redirectToRoute('admin_user_schedule', [
                'id'        => $operator->getId(),
                'filial_id' => array_shift($filialChoices),
            ]);
        }

        $fb = $this->createFormBuilder([], [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);

        $fb->add('filial', ChoiceType::class, [
            'label'             => 'Филиал',
            'choices_as_values' => true,
            'expanded'          => true,
            'choices'           => $filialChoices,
        ]);

        $fb->add('save', SubmitType::class, [
            'label' => 'Продолжить',
            'attr'  => ['class' => 'btn-success btn-lg'],
        ]);

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->redirectToRoute('admin_user_schedule', [
                'id'        => $operator->getId(),
                'filial_id' => $form->get('filial')->getData(),
            ]);
        }

        return $this->render('AdminBundle:User:select_filial.html.twig', [
            'form'          => $form->createView(),
            'operator'      => $operator,
            'filialChoices' => $filialChoices,
        ]);
    }
}
