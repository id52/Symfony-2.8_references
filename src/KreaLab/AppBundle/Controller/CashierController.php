<?php

namespace KreaLab\AppBundle\Controller;

use KreaLab\CommonBundle\Entity\CashierGettingLog;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class CashierController extends Controller
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
        $this->denyAccessUnlessGranted('ROLE_CASHIER');
    }

    /**
     * @Route("/cashier-get/", name="cashier_get")
     * @Template
     */
    public function getAction(Request $request)
    {
        $id = $request->get('id', 0);
        if (!$id) {
            $supervisors = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
                ->andWhere('u.active = :active')->setParameter('active', true)
                ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_SUPERVISOR%')
                ->addOrderBy('u.last_name')
                ->addOrderBy('u.first_name')
                ->addOrderBy('u.patronymic')
                ->getQuery()->execute();
            return $this->render('AppBundle:Cashier:get_list.html.twig', [
                'supervisors' => $supervisors,
            ]);
        }

        /** @var $supervisor \KreaLab\CommonBundle\Entity\User */
        $supervisor = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
            ->andWhere('u.active = :active')->setParameter('active', true)
            ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_SUPERVISOR%')
            ->andWhere('u.id = :id')->setParameter('id', $id)
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('sum', IntegerType::class, [
            'label'       => 'Сумма',
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\LessThanOrEqual($supervisor->getSupervisorSum()),
                new Assert\GreaterThan(0),
            ],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $sum = intval($form->get('sum')->getData());

            $cgl = new CashierGettingLog();
            $cgl->setSupervisor($supervisor);
            $cgl->setCashier($this->getUser());
            $cgl->setSum($sum);
            $this->em->persist($cgl);

            $supervisor->setSupervisorSum($supervisor->getSupervisorSum() - $sum);
            $this->em->persist($supervisor);

            $this->em->flush();

            $this->addFlash('success', 'Деньги успешно забраны');
            return $this->redirectToRoute('cashier_get');
        }

        return [
            'form'       => $form->createView(),
            'supervisor' => $supervisor,
        ];
    }
}
