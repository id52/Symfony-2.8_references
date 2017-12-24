<?php

namespace KreaLab\AppBundle\Controller;

use Doctrine\ORM\EntityRepository;
use KreaLab\AdminSkeletonBundle\Form\Type\Measure;
use KreaLab\CommonBundle\Entity\SupervisorGettingLog;
use KreaLab\CommonBundle\Entity\SupervisorRepayment;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class SupervisorController extends Controller
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
        $this->denyAccessUnlessGranted('ROLE_SUPERVISOR');
    }

    /**
     * @Route("/supervisor-get-evelopes/", name="supervisor_get_evelopes")
     * @Template("AppBundle:Supervisor:get_evelopes.html.twig")
     */
    public function getEvelopesAction(Request $request)
    {
        $id = intval($request->get('id'));
        if (!$id) {
            $couriers = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
                ->addOrderBy('u.last_name')
                ->addOrderBy('u.first_name')
                ->addOrderBy('u.patronymic')
                ->leftJoin('u.courier_envelopes', 'e', 'WITH', 'e.supervisor IS NULL')
                ->addSelect('COUNT(e.id) AS e_cnt')
                ->having('e_cnt > 0')
                ->addSelect('SUM(e.sum) AS e_sum')
                ->addGroupBy('u.id')
                ->getQuery()->execute();
            return $this->render('AppBundle:Supervisor:get_evelopes_list.html.twig', ['couriers' => $couriers]);
        }

        $courier = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
            ->andWhere('u.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        $envelopes = [];
        if ($courier) {
            $envelopes = $this->em->getRepository('CommonBundle:Envelope')->createQueryBuilder('e')
                ->leftJoin('e.workplace', 'w')->addSelect('w')
                ->leftJoin('w.filial', 'f')->addSelect('f')
                ->andWhere('e.courier = :courier')->setParameter('courier', $courier)
                ->andWhere('e.supervisor IS NULL')
                ->addOrderBy('f.name')
                ->addOrderBy('w.name')
                ->getQuery()->execute();
        }

        $choices = [];
        foreach ($envelopes as $envelope) { /** @var $envelope \KreaLab\CommonBundle\Entity\Envelope */
            $choices[$envelope->getId()] = $envelope->getId();
        }

        $fb = $this->createFormBuilder(null, [
            'translation_domain' => false,
        ]);
        $fb->add('envelopes', ChoiceType::class, [
            'choices_as_values' => true,
            'multiple'          => true,
            'expanded'          => true,
            'choices'           => $choices,
            'constraints'       => new Assert\NotBlank(['message' => 'blank_evelopes']),
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $sgl = new SupervisorGettingLog();
            $sgl->setCourier($courier);
            $sgl->setSupervisor($this->getUser());
            $sum       = 0;
            $envelopes = $this->em->getRepository('CommonBundle:Envelope')->findBy([
                'id' => $form->get('envelopes')->getData(),
            ]);
            foreach ($envelopes as $envelope) { /** @var $envelope \KreaLab\CommonBundle\Entity\Envelope */
                $envelope->setSgl($sgl);
                $envelope->setSupervisor($this->getUser());
                $envelope->setSupervisorDatetime(new \DateTime());
                $this->em->persist($envelope);
                $sum += $envelope->getSum();
            }

            $sgl->setSum($sum);
            $this->em->persist($sgl);
            $this->em->flush();

            return $this->redirectToRoute('supervisor_on_hands');
        }

        return [
            'form'      => $form->createView(),
            'envelopes' => $envelopes,
        ];
    }

    /**
     * @Route("/supervisor-on-hands/", name="supervisor_on_hands")
     * @Template("AppBundle:Supervisor:on_hands.html.twig")
     */
    public function onHandsAction()
    {
        $envelopes = $this->em->getRepository('CommonBundle:Envelope')->createQueryBuilder('e')
            ->andWhere('e.supervisor = :supervisor')->setParameter('supervisor', $this->getUser())
            ->andWhere('e.supervisor_accepted_at IS NULL')
            ->getQuery()->execute();
        return ['envelopes' => $envelopes];
    }

    /**
     * @Route("/supervisor-take-envelope-{id}/", name="supervisor_take_evelope")
     */
    public function takeEnvelopeAction($id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\Envelope */
        $envelope = $this->em->getRepository('CommonBundle:Envelope')->createQueryBuilder('e')
            ->andWhere('e.id = :id')->setParameter('id', $id)
            ->andWhere('e.supervisor = :supervisor')->setParameter('supervisor', $this->getUser())
            ->andWhere('e.supervisor_accepted_at IS NULL')
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$envelope) {
            throw $this->createNotFoundException();
        }

        $supervisor = $this->getUser();
        $supervisor->setSupervisorSum($supervisor->getSupervisorSum() + $envelope->getSum());
        $this->em->persist($supervisor);

        $envelope->setSupervisorAcceptedAt(new \DateTime());
        $this->em->persist($envelope);

        $this->em->flush();

        $this->addFlash('success', 'Конверт №'.$envelope->getId().' принят на баланс');
        return $this->redirectToRoute('supervisor_on_hands');
    }

    /**
     * @Route("/supervisor-orders/", name="supervisor_orders")
     */
    public function ordersAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:Order')->createQueryBuilder('o')
            ->andWhere('o.status IN (:statuses)')->setParameter('statuses', [
                'issuedByOperator',
            ])
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Supervisor/Order:list.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /**
     * @Route("/supervisor-orders/take/", name="supervisor_orders_take")
     */
    public function takeOrderAction(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('orderman', EntityType::class, [
            'label'         => 'Ордерист',
            'placeholder'   => ' - Выберите ордериста - ',
            'constraints'   => new Assert\NotBlank(),
            'class'         => 'CommonBundle:User',
            'choice_label'  => 'ordermanNameWithSum',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.active = :active')->setParameter('active', true)
                    ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_ORDERMAN%')
                    ->addOrderBy('u.id')
                ;
            },
        ]);
        $fb->add('sum', Measure::class, [
            'label'       => 'Сумма',
            'measure'     => 'руб.',
            'attr'        => ['bsize' => 3],
            'constraints' => [new Assert\NotBlank(), new Assert\GreaterThan(0)],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($request->isMethod('post')) {
            $supervisor = $this->getUser();
            $orderman   = $form->get('orderman')->getData();
            $sum        = intval($form->get('sum')->getData());

            if ($orderman->getOrdermanSum() < $sum) {
                $form->get('sum')->addError(new FormError('Недостаточно средств'));
            }

            if ($form->isValid()) {
                $supervisor->setSupervisorSum($supervisor->getSupervisorSum() + $sum);
                $this->em->persist($supervisor);

                $orderman->setOrdermanSum($orderman->getOrdermanSum() - $sum);
                $this->em->persist($orderman);
                $this->em->flush();

                $this->addFlash('success', 'Вы забрали '.$sum.' руб. у ордериста '.$orderman->getFullName());
                return $this->redirectToRoute('supervisor_orders');
            }
        }

        return $this->render('AppBundle:Supervisor/Order:take.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/supervisor-orders/repay-{id}/", name="supervisor_orders_repay")
     */
    public function repayAction(Request $request, $id)
    {
        /** @var $order \KreaLab\CommonBundle\Entity\Order */
        $order = $this->em->getRepository('CommonBundle:Order')->createQueryBuilder('o')
            ->andWhere('o.id = :id')->setParameter('id', $id)
            ->andWhere('o.status IN (:statuses)')->setParameter('statuses', ['issuedByOperator'])
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$order) {
            throw $this->createNotFoundException();
        }

        $fb = $this->createFormBuilder(['sum' => $order->getResidual()], ['translation_domain' => false]);
        $fb->add('sum', Measure::class, [
            'label'       => 'Сумма',
            'measure'     => 'руб',
            'attr'        => ['fsize' => 5],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\GreaterThanOrEqual(0),
                new Assert\LessThanOrEqual($order->getResidual()),
            ],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $supervisor = $this->getUser();
            $sum        = intval($form->get('sum')->getData());
            $residual   = $order->getResidual() - $sum;

            $supervisor->setSupervisorSum($supervisor->getSupervisorSum() + $sum);
            $this->em->persist($supervisor);

            $order->setResidual($residual);
            if ($residual == 0) {
                $order->setStatus('closedBySupervisor');
            }

            $this->em->persist($order);

            $supervisorRepayment = new SupervisorRepayment();
            $supervisorRepayment->setSum($sum);
            $supervisorRepayment->setOrder($order);
            $supervisorRepayment->setSupervisor($supervisor);
            $this->em->persist($supervisorRepayment);

            $this->em->flush();

            $this->addFlash('success', 'Ордер №'.$order->getId().' погашен на '.$sum.' руб.');
            return $this->redirectToRoute('supervisor_orders');
        }

        return $this->render('AppBundle:Supervisor/Order:repay.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/supervisor-view-envelope-{id}/", name="supervisor_view_evelope")
     */
    public function viewEnvelopeAction(Request $request, $id)
    {
        /** @var $envelope \KreaLab\CommonBundle\Entity\Envelope */
        $envelope = $this->em->getRepository('CommonBundle:Envelope')->createQueryBuilder('e')
            ->andWhere('e.id = :id')->setParameter('id', $id)
            ->andWhere('e.supervisor = :supervisor')->setParameter('supervisor', $this->getUser())
            ->andWhere('e.supervisor_accepted_at IS NULL')
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();

        if (!$envelope) {
            throw $this->createNotFoundException();
        }

        $serviceLogs = $envelope->getServiceLogs();
        $orders      = $envelope->getOrders();
        $items       = [];

        foreach ($serviceLogs as $serviceLog) { /** @var $serviceLog \KreaLab\CommonBundle\Entity\ServiceLog */
            $item               = [];
            $item['id']         = $serviceLog->getId();
            $item['created_at'] = $serviceLog->getCreatedAt()->format('Y-m-d H:i:s');
            $item['service']    = $serviceLog->getService();
            $item['filial']     = $serviceLog->getWorkplace()->getFilial();
            $item['workplace']  = $serviceLog->getWorkplace();
            $item['cashbox']    = $serviceLog->getCashbox();
            $item['sum']        = $serviceLog->getSum();
            $item['courier']    = $envelope->getCourier();
            $items[]            = $item;
        }

        foreach ($orders as $order) { /** @var $order \KreaLab\CommonBundle\Entity\Order */
            $item               = [];
            $item['id']         = $order->getId();
            $item['created_at'] = $order->getUpdatedAt()->format('Y-m-d H:i:s');
            $item['service']    = 'Ордер';
            $item['filial']     = $order->getWorkplace()->getFilial();
            $item['workplace']  = $order->getWorkplace();
            $item['cashbox']    = '';
            $item['sum']        = -$order->getSum();
            $item['courier']    = $envelope->getCourier();
            $items[]            = $item;
        }

        usort($items, function ($first, $second) {
            return ($first['created_at'] < $second['created_at']) ? -1 : 1;
        });

        $fb   = $this->createFormBuilder([], [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->redirectToRoute('supervisor_take_evelope', ['id' => $id]);
        }

        return $this->render('AppBundle:Supervisor/Order:view_envelope.html.twig', [
            'form'  => $form->createView(),
            'items' => $items,
        ]);
    }
}
