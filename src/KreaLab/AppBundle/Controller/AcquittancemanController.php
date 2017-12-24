<?php

namespace KreaLab\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

class AcquittancemanController extends Controller
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
        $this->denyAccessUnlessGranted('ROLE_ACQUITTANCEMAN');
    }

    /**
     * @Route("/acquittanceman-orders/", name="acquittanceman_orders")
     */
    public function ordersAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:Order')->createQueryBuilder('o')
            ->andWhere('o.status IN (:statuses)')->setParameter('statuses', [
                'createdByTreasurer',
                'issuedByOperator',
                'forkedByOrderman',
            ])
            ->andWhere('o.acquittanceman = :acquittanceman')->setParameter('acquittanceman', $this->getUser())
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Acquittanceman:orders.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }
}
