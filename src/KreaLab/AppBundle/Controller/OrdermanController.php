<?php

namespace KreaLab\AppBundle\Controller;

use KreaLab\CommonBundle\Entity\Consumable;
use KreaLab\CommonBundle\Entity\Order;
use KreaLab\CommonBundle\Entity\OrdermanConsumableBox;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use KreaLab\AdminSkeletonBundle\Form\Type\Measure;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Doctrine\ORM\EntityRepository;

class OrdermanController extends Controller
{
    /** @var $em \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
        $this->denyAccessUnlessGranted('ROLE_ORDERMAN');
    }

    /** @Route("/orderman-orders/", name="orderman_orders") */
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

        return $this->render('AppBundle:Orderman:list.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /** @Route("/orderman-orders/order-{id}/", name="orderman_orders_order") */
    public function orderAction($id)
    {
        $order = $this->em->getRepository('CommonBundle:Order')->createQueryBuilder('o')
            ->andWhere('o.id = :id')->setParameter('id', $id)
            ->andWhere('o.status IN (:statuses)')->setParameter('statuses', [
                'issuedByOperator',
            ])
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$order) {
            throw $this->createNotFoundException();
        }

        $supervisorRepayments = $this->em->getRepository('CommonBundle:SupervisorRepayment')->findBy(['order' => $id]);
        $consumables          = $this->em->getRepository('CommonBundle:Consumable')->findBy(['order' => $id]);

        $closeSum = $this->em->getRepository('AdminSkeletonBundle:Setting')->get('orderman_close_order_sum', 1000);

        return $this->render('AppBundle:Orderman:item.html.twig', [
            'order'                => $order,
            'supervisorRepayments' => $supervisorRepayments,
            'consumables'          => $consumables,
            'close_sum'            => $closeSum,
        ]);
    }

    /**
     * @Route("/orderman-orders/consumable-{id}/", name="orderman_consumable")
     */
    public function consumableAction($id)
    {
        $consumable = $this->em->find('CommonBundle:Consumable', $id);
        if (!$consumable) {
            throw $this->createNotFoundException();
        }

        return $this->render('AppBundle:Orderman:consumable.html.twig', [
            'consumable' => $consumable,
        ]);
    }

    /**
     * @Route("/orderman-orders/add-consumable-{orderId}/", name="orderman_consumable_add")
     */
    public function addConsumableAction(Request $request, $orderId)
    {
        /** @var $order \KreaLab\CommonBundle\Entity\Order */
        $order = $this->em->getRepository('CommonBundle:Order')->createQueryBuilder('o')
            ->andWhere('o.id = :id')->setParameter('id', $orderId)
            ->andWhere('o.status IN (:statuses)')->setParameter('statuses', [
                'issuedByOperator',
            ])
            ->andWhere('o.parent IS NULL')
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$order) {
            throw $this->createNotFoundException();
        }

        $consumable = new Consumable();
        $fb         = $this->createFormBuilder($consumable, ['translation_domain' => false]);
        $fb->add('consumable_doc_type', EntityType::class, [
            'label'         => 'Тип расходника',
            'placeholder'   => ' - Выберите тип документа - ',
            'class'         => 'CommonBundle:ConsumableDocType',
            'constraints'   => new Assert\NotBlank(),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('cdt')
                    ->andWhere('cdt.active = :active')->setParameter('active', true)
                ;
            },
        ]);
        $fb->add('name', TextType::class, [
            'label'       => 'Номер расходника',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('doc_date', DateType::class, [
            'label'       => 'Дата расходника',
            'constraints' => new Assert\NotBlank(),
            'data'        => new \DateTime(),
        ]);
        $fb->add('description', TextareaType::class, [
            'label'       => 'Описание',
            'constraints' => new Assert\NotBlank(),
            'attr'        => ['class' => 'ckeditor'],
        ]);

        $tagCategories = $this->em->getRepository('CommonBundle:ConsumableTagCategory')
            ->findBy(['active' => true], ['position' => 'ASC']);

        $tagChoices           = [];
        $tagCategoriesChoices = [];
        $tagCategoriesSelect  = []; /** @var $tagCategory \KreaLab\CommonBundle\Entity\ConsumableTagCategory */
        foreach ($tagCategories as $tagCategory) {
            if (count($tagCategory->getTags()) > 0) {
                $tagCategoriesSelect[$tagCategory->getId()]    = [];
                $tagCategoriesChoices[$tagCategory->getName()] = $tagCategory->getId();
                foreach ($tagCategory->getTags() as $tag) { /** @var $tag \KreaLab\CommonBundle\Entity\ConsumableTag */
                    $tagChoices[$tag->getName()]                  = $tag->getId();
                    $tagCategoriesSelect[$tagCategory->getId()][] = $tag->getId();
                }
            }
        }

        $fb->add('tag_category', ChoiceType::class, [
            'choices'           => $tagCategoriesChoices,
            'choices_as_values' => true,
            'expanded'          => true,
            'mapped'            => false,
        ]);
        $fb->add('tag', ChoiceType::class, [
            'label'             => 'Тег',
            'choices'           => $tagChoices,
            'choices_as_values' => true,
            'expanded'          => true,
            'constraints'       => new Assert\NotBlank(),
            'mapped'            => false,
        ]);

        $ordermanOverrun = $this->em->getRepository('AdminSkeletonBundle:Setting')->get('orderman_overrun', 0);
        $sum             = $order->getSum() * $ordermanOverrun + $order->getResidual();

        $fb->add('sum', Measure::class, [
            'label'       => 'Сумма',
            'measure'     => 'руб',
            'attr'        => ['bsize' => 3],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\GreaterThan(0),
                new Assert\LessThanOrEqual($sum),
            ],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $consumable->setOrderman($this->getUser());
            $consumable->setOrder($order);
            $this->em->persist($consumable);
            $this->em->flush();

            $tag = $this->em->find('CommonBundle:ConsumableTag', intval($form->get('tag')->getData()));
            if ($tag) {
                $tag->addConsumable($consumable);
                $this->em->persist($tag);
            }

            $order->setResidual($order->getResidual() - $form->get('sum')->getData());
            $this->em->persist($order);

            $this->em->flush();

            return $this->redirectToRoute('orderman__select_consumable_box', ['id' => $consumable->getId()]);
        }

        return $this->render('AppBundle:Orderman:consumable_add.html.twig', [
            'form'           => $form->createView(),
            'order_id'       => $orderId,
            'tag_categories' => $tagCategoriesSelect,
        ]);
    }

    /**
     * @Route("/orderman-orders/edit-consumable-{consumableId}/", name="orderman_consumable_edit")
     */
    public function editConsumableAction(Request $request, $consumableId)
    {
        $consumable = $this->em->find('CommonBundle:Consumable', $consumableId);
        if (!$consumable or $consumable->getOrder()->getStatus() != 'issuedByOperator') {
            throw $this->createNotFoundException();
        }

        $fb = $this->createFormBuilder($consumable, ['translation_domain' => false]);
        $fb->add('consumable_doc_type', EntityType::class, [
            'label'         => 'Тип документа',
            'placeholder'   => ' - Выберите тип документа - ',
            'class'         => 'CommonBundle:ConsumableDocType',
            'constraints'   => new Assert\NotBlank(),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('cdt')
                    ->andWhere('cdt.active = :active')->setParameter('active', true)
                ;
            },
        ]);
        $fb->add('doc_date', DateType::class, [
            'label'       => 'Дата документа',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('name', TextType::class, [
            'label'       => 'Название',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('description', TextareaType::class, [
            'label'       => 'Описание',
            'constraints' => new Assert\NotBlank(),
            'attr'        => ['class' => 'ckeditor'],
        ]);

        $tagCategories = $this->em->getRepository('CommonBundle:ConsumableTagCategory')
            ->findBy(['active' => true], ['position' => 'ASC']);

        $tagChoices           = [];
        $tagCategoriesChoices = [];
        $tagCategoriesSelect  = []; /** @var $tagCategory \KreaLab\CommonBundle\Entity\ConsumableTagCategory */
        foreach ($tagCategories as $tagCategory) {
            if (count($tagCategory->getTags()) > 0) {
                $tagCategoriesSelect[$tagCategory->getId()]    = [];
                $tagCategoriesChoices[$tagCategory->getName()] = $tagCategory->getId();
                foreach ($tagCategory->getTags() as $tag) { /** @var $tag \KreaLab\CommonBundle\Entity\ConsumableTag */
                    $tagChoices[$tag->getName()]                  = $tag->getId();
                    $tagCategoriesSelect[$tagCategory->getId()][] = $tag->getId();
                }
            }
        }

        $fb->add('tag_category', ChoiceType::class, [
            'choices'           => $tagCategoriesChoices,
            'choices_as_values' => true,
            'expanded'          => true,
            'mapped'            => false,
        ]);
        $fb->add('tag', ChoiceType::class, [
            'label'             => 'Тег',
            'choices'           => $tagChoices,
            'choices_as_values' => true,
            'expanded'          => true,
            'constraints'       => new Assert\NotBlank(),
            'mapped'            => false,
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            foreach ($consumable->getTags() as $tag) {
                $tag->removeConsumable($consumable);
                $this->em->persist($tag);
            }

            $this->em->flush();

            $consumable->setOrderman($this->getUser());

            $tag = $this->em->find('CommonBundle:ConsumableTag', intval($form->get('tag')->getData()));
            if ($tag) {
                $tag->addConsumable($consumable);
                $this->em->persist($tag);
            }

            $this->em->persist($consumable);
            $this->em->flush();

            $this->addFlash('success', 'Расходник обновлён');
            return $this->redirectToRoute('orderman_consumable', ['id' => $consumable->getId()]);
        }

        $tagSelected         = null;
        $tagCategorySelected = null;
        if (!empty($consumable->getTags())) {
            $tagSelected         = $consumable->getTags()[0];
            $tagCategorySelected = $tagSelected->getTagCategory()->getId();
            $tagSelected         = $tagSelected->getId();
        }

        return $this->render('AppBundle:Orderman:consumable_edit.html.twig', [
            'form'                  => $form->createView(),
            'order_id'              => $consumable->getOrder()->getId(),
            'tag_categories'        => $tagCategoriesSelect,
            'tag_selected'          => $tagSelected,
            'tag_category_selected' => $tagCategorySelected,
        ]);
    }

    /**
     * @Route("/orderman-orders/close-{id}/", name="orderman_orders_order_close")
     */
    public function closeOrderAction($id)
    {
        $order = $this->em->getRepository('CommonBundle:Order')->createQueryBuilder('o')
            ->andWhere('o.id = :id')->setParameter('id', $id)
            ->andWhere('o.status IN (:statuses)')->setParameter('statuses', [
                'issuedByOperator',
            ])
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        if (!$order) {
            throw $this->createNotFoundException();
        }

        if ($order->getResidual() < 0) {
            $suborder = new Order();
            $suborder->setAcquittanceman($order->getAcquittanceman());
            $suborder->setAppointment($order->getAppointment());
            $suborder->setPin(random_int(1000, 9999));
            $suborder->setSum(abs($order->getResidual()));
            $suborder->setResidual(abs($order->getResidual()));
            $suborder->setOrderType($order->getOrderType());
            $suborder->setDescription($order->getDescription());
            $suborder->setParent($order);
            $suborder->setStatus('forkedByOrderman');

            $this->em->persist($suborder);
            $this->em->flush();

            if ($suborder->getAcquittanceman() and !empty($suborder->getAcquittanceman()->getPhone())) {
                $sum  = number_format($suborder->getSum(), 0, ',', ' ');
                $text = $suborder->getId().'; '.$sum.'; '.$suborder->getPin();
                $this->get('sms_uslugi_ru')->send('+7'.$suborder->getAcquittanceman()->getPhone(), $text);
            }

            $order->addChild($suborder);
        } else {
            $orderman = $this->getUser();
            $orderman->setOrdermanSum($orderman->getOrdermanSum() + $order->getResidual());
            $this->em->persist($orderman);
        }

        $order->setResidual(0);
        $order->setStatus('closedByOrderman');
        $this->em->persist($order);

        $this->em->flush();

        $this->addFlash('success', 'Ордер закрыт');
        return $this->redirectToRoute('orderman_orders');
    }

    /** @Route("/orderman-orders/select-consumable-box-{id}/",
     *      name="orderman__select_consumable_box") */
    public function consumableBoxSelectAction(Request $request, $id)
    {
        $box = $this->em->getRepository('CommonBundle:OrdermanConsumableBox')->findOneBy([
            'closed_at' => null,
            'orderman'  => $this->getUser(),
        ]);

        if ($request->isMethod('post')) {
            $oldBox = null;

            /** @var $consumable \KreaLab\CommonBundle\Entity\Consumable */
            $consumable = $this->em->getRepository('CommonBundle:Consumable')->findOneBy([
                'orderman' => $this->getUser(),
                'id'       => $id,
            ]);

            if (!$consumable) {
                throw $this->createNotFoundException('Нет расходника');
            }

            if (!$box) {
                $box = new OrdermanConsumableBox();
                $box->setOrderman($this->getUser());
                $box->setAcquittanceman($consumable->getOrder()->getAcquittanceman());
            }

            if ($request->get('action') == 'close') {
                $box->setClosedAt(new \DateTime());
                $this->em->persist($box);
                $this->em->flush();
                $oldBox = $box;

                $box = new OrdermanConsumableBox();
                $box->setAcquittanceman($consumable->getOrder()->getAcquittanceman());
                $box->setOrderman($this->getUser());
            }

            $box->addConsumable($consumable);
            $box->setAcquittanceman($consumable->getOrder()->getAcquittanceman());
            $this->em->persist($box);
            $this->em->flush();
            $consumable->setOrdermanConsumableBox($box);
            $this->em->persist($consumable);
            $this->em->flush();

            $action = $request->get('action');

            if ($action) {
                return $this->render('AppBundle:Orderman:orderman_consumable_box__info.html.twig', [
                    'action'     => $action,
                    'oldBox'     => $oldBox,
                    'box'        => $box,
                    'consumable' => $consumable,
                ]);
            } else {
                return $this->redirectToRoute('orderman_orders');
            }
        };

        if (empty($box)) {
            $text = $this->em->getRepository('AdminSkeletonBundle:Setting')->findOneBy([
                '_key' => 'creating_orderman_archive_box_text',
            ]);
            $text = $text->getValue();
        } else {
            $text = $this->em->getRepository('AdminSkeletonBundle:Setting')->findOneBy([
                '_key' => 'current_orderman_archive_box_text',
            ]);

            if ($text) {
                $text = $text->getValue();
                $text = str_replace('{{ number_box }}', $box->getId(), $text);
            }
        }

        return $this->render('AppBundle:Orderman:orderman_consumable_box__select.html.twig', [
            'box'  => $box,
            'text' => $text,
        ]);
    }

    /** @Route("/orderman/consumable-boxes/",
     *  name="orderman__consumable_boxes") */
    public function consumableBoxesAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:OrdermanConsumableBox')
            ->createQueryBuilder('b')
            ->andWhere('b.orderman = :orderman')->setParameter('orderman', $this->getUser())
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Orderman:consumable_boxes.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /** @Route("/orderman/consumable-boxes/view-{id}/",
     *  name="orderman__consumable_box") */
    public function consumableBoxAction($id)
    {
        $box = null;

        $box = $this->em->getRepository('CommonBundle:OrdermanConsumableBox')
            ->createQueryBuilder('box')
            ->leftJoin('box.consumables', 'c')->addSelect('c')
            ->andWhere('box.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        return $this->render('AppBundle:Orderman:consumable_box.html.twig', [
            'box' => $box,
        ]);
    }
}
