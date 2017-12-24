<?php

namespace KreaLab\AppBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use KreaLab\AdminSkeletonBundle\Form\Type\Measure;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use KreaLab\CommonBundle\Entity\Order;

class TreasurerController extends Controller
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
        $this->denyAccessUnlessGranted('ROLE_TREASURER');
    }

    /** @Route("/treasurer-orders/add/", name="treasurer_orders_add") */
    public function addOrderAction(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('acquittanceman', EntityType::class, [
            'placeholder'   => ' - Выберите расписчика - ',
            'label'         => 'Расписчик',
            'constraints'   => new Assert\NotBlank(),
            'class'         => 'CommonBundle:User',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->andWhere('u.active = :active')->setParameter('active', true)
                    ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_ACQUITTANCEMAN%')
                    ->addOrderBy('u.id');
            },
        ]);
        $fb->add('appointment', TextType::class, [
            'label'       => 'Назначение',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('description', TextareaType::class, [
            'label'       => 'Описание',
            'constraints' => new Assert\NotBlank(),
            'attr'        => ['class' => 'ckeditor'],
        ]);
        $fb->add('sum', Measure::class, [
            'label'       => 'Сумма',
            'measure'     => 'руб',
            'attr'        => ['fsize' => 3],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\GreaterThan(0),
            ],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $workplaceId = intval($this->get('session')->get('workplace'));
            $workplace   = $this->em->getRepository('CommonBundle:Workplace')->find($workplaceId);

            $order = new Order();
            $order->setAcquittanceman($form->get('acquittanceman')->getData());
            $order->setTreasurer($this->getUser());
            $order->setWorkplace($workplace);
            $order->setAppointment($form->get('appointment')->getData());
            $order->setPin(random_int(1000, 9999));
            $order->setSum($form->get('sum')->getData());
            $order->setResidual($form->get('sum')->getData());
            $order->setDescription(($form->get('description')->getData()));
            $order->setStatus('createdByTreasurer');
            $this->em->persist($order);
            $this->em->flush();

            $acquittanceman = $this->em->getRepository('CommonBundle:User')
                ->find($form->get('acquittanceman')->getData());

            if ($acquittanceman and !empty($acquittanceman->getPhone())) {
                $sum  = number_format($order->getSum(), 0, ',', ' ');
                $text = $order->getId().'; '.$sum.'; '.$order->getPin();
                $this->get('sms_uslugi_ru')->send('+7'.$acquittanceman->getPhone(), $text);
            }

            $this->addFlash('success', 'Пинкод: '.$order->getPin());
            return $this->redirectToRoute('treasurer_orders');
        }

        return $this->render('AppBundle:Treasurer:item.html.twig', ['form' => $form->createView()]);
    }

    /** @Route("/treasurer-orders/delete-{id}/", name="treasurer_orders_delete") */
    public function deleteOrderAction($id)
    {
        $order = $this->em->getRepository('CommonBundle:Order')->findOneBy([
            'id'        => $id,
            'status'    => 'createdByTreasurer',
            'treasurer' => $this->getUser(),
        ]);

        if ($order) {
            $supervisorRepayments = $order->getSupervisorRepayments();
            foreach ($supervisorRepayments as $supervisorRepayment) {
                $this->em->remove($supervisorRepayment);
            }

            $this->em->remove($order);
            $this->em->flush();
            $this->addFlash('success', 'Удалили.');
            return $this->redirectToRoute('treasurer_orders');
        } else {
            throw $this->createNotFoundException();
        }
    }

    /** @Route("/treasurer-orders/", name="treasurer_orders") */
    public function ordersAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:Order')->createQueryBuilder('o')
            ->andWhere('o.status = :status')->setParameter('status', 'createdByTreasurer')
            ->andWhere('o.treasurer = :treasurer')->setParameter('treasurer', $this->getUser())
            ->addOrderBy('o.created_at')
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Treasurer:list.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }
}
