<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;
use KreaLab\CommonBundle\Entity\ServiceDiscount;

class DiscountController extends AbstractEntityController
{
    protected $orderBy  = ['position' => 'ASC'];
    protected $tmplItem = 'AdminBundle:Discount:item.html.twig';
    protected $tmplList = 'AdminBundle:Discount:list.html.twig';

    /**
     * @param $entity \KreaLab\CommonBundle\Entity\Discount
     * @return \KreaLab\CommonBundle\Entity\Discount
     */
    public function prePersist($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        $this->em->getRepository('CommonBundle:ServiceDiscount')->createQueryBuilder('sd')
            ->delete()
            ->andWhere('sd.discount = :discount')->setParameter('discount', $entity)
            ->getQuery()->execute();

        $request         = $this->get('request_stack')->getCurrentRequest();
        $services        = $this->em->getRepository('CommonBundle:Service')->findBy([], ['name' => 'ASC']);
        $discounts       = (array)$request->get('discounts', []);
        $discountsActive = (array)$request->get('discounts_active', []);

        foreach ($services as $service) { /** @var $service \KreaLab\CommonBundle\Entity\Service */
            $discount = new ServiceDiscount();
            $discount->setSum(isset($discounts[$service->getId()]) ? max(intval($discounts[$service->getId()]), 0) : 0);
            $discount->setActive(isset($discountsActive[$service->getId()]));
            $discount->setService($service);
            $discount->setDiscount($entity);
            $this->em->persist($discount);
        }

        return $entity;
    }

    /**
     * @param $entity \KreaLab\CommonBundle\Entity\Discount
     * @return array
     */
    public function getRenderExtraOptions($entity)
    {
        $request           = $this->get('request_stack')->getCurrentRequest();
        $services          = $this->em->getRepository('CommonBundle:Service')->findBy([], ['position' => 'ASC']);
        $discounts         = [];
        $discountsActive   = [];
        $servicesDiscounts = $entity->getServicesDiscounts();

        foreach ($servicesDiscounts as $discount) { /** @var $discount \KreaLab\CommonBundle\Entity\ServiceDiscount */
            $discounts[$discount->getService()->getId()]       = $discount->getSum();
            $discountsActive[$discount->getService()->getId()] = $discount->getActive();
        }

        $discounts       = (array)$request->get('discounts', []) + $discounts;
        $discountsActive = (array)$request->get('discounts_active', []) + $discountsActive;

        return [
            'services'         => $services,
            'discounts'        => $discounts,
            'discounts_active' => $discountsActive,
        ];
    }
}
