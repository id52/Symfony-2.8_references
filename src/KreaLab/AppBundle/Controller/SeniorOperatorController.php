<?php

namespace KreaLab\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SeniorOperatorController extends Controller
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
        $this->denyAccessUnlessGranted('ROLE_SENIOR_OPERATOR');
    }

    /**
     * @Route("/senior-current/", name="senior_current")
     * @Template
     */
    public function currentAction()
    {
        $wIds    = [];
        $filials = $this->getUser()->getFilials();
        foreach ($filials as $filial) {
            /** @var $filial \KreaLab\CommonBundle\Entity\Filial */
            foreach ($filial->getActiveWorkplaces() as $workplace) {
                /** @var $workplace \KreaLab\CommonBundle\Entity\Workplace */

                $wIds[] = $workplace->getId();
            }
        }

        $logs  = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
            ->andWhere('sl.workplace IN (:wIds)')->setParameter('wIds', $wIds)
            ->andWhere('sl.envelope IS NULL')
            ->andWhere('sl.import = 0')
            ->addOrderBy('sl.created_at')
            ->getQuery()->execute();
        $items = [];
        foreach ($logs as $log) { /** @var $log \KreaLab\CommonBundle\Entity\ServiceLog */
            $item               = [];
            $item['id']         = $log->getId();
            $item['created_at'] = $log->getCreatedAt()->format('Y-m-d H:i:s');
            $item['service']    = $log->getService();
            $item['filial']     = $log->getWorkplace()->getFilial();
            $item['workplace']  = $log->getWorkplace();
            $item['cashbox']    = $log->getCashbox();
            $item['sum']        = $log->getSum();
            $items[]            = $item;
        }

        $orders = $this->em->getRepository('CommonBundle:Order')->createQueryBuilder('e')
            ->andWhere('e.workplace IN (:wIds)')->setParameter('wIds', $wIds)
            ->andWhere('e.envelope IS NULL')
            ->addOrderBy('e.updated_at')
            ->getQuery()->execute();
        foreach ($orders as $order) { /** @var $order \KreaLab\CommonBundle\Entity\Order */
            $item               = [];
            $item['id']         = $order->getId();
            $item['created_at'] = $order->getUpdatedAt()->format('Y-m-d H:i:s');
            $item['service']    = 'Ордер';
            $item['filial']     = $order->getWorkplace()->getFilial();
            $item['workplace']  = $order->getWorkplace();
            $item['cashbox']    = '';
            $item['sum']        = -$order->getSum();
            $items[]            = $item;
        }

        usort($items, function ($first, $second) {
            return ($first['created_at'] < $second['created_at']) ? -1 : 1;
        });

        return ['items' => $items];
    }
}
