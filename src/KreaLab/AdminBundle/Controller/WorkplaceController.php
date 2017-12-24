<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;

class WorkplaceController extends AbstractEntityController
{
    protected $listFields = [['id', 'min_col'], 'filial', 'name', ['legal_entity_short_name', 'min_col']];
    protected $orderBy    = ['active' => 'DESC'];
    protected $perms      = ['ROLE_MANAGE_FILIALS'];
    protected $tmplItem   = 'AdminBundle:Workplace:item.html.twig';
    protected $tmplList   = 'AdminBundle:Workplace:list.html.twig';

    public function listQb()
    {
        $qb = parent::listQb();
        $qb->leftJoin('e.filial', 'f')->addSelect('f');
        $qb->addOrderBy('f.name');
        $qb->addOrderBy('e.name');
        return $qb;
    }

    public function prePersist($entity)
    {
        /** @var $entity \KreaLab\CommonBundle\Entity\Workplace */
        $ids       = [];
        $cashboxes = $entity->getCashboxes();
        foreach ($cashboxes as $cashbox) { /** @var $cashbox \KreaLab\CommonBundle\Entity\Cashbox */
            $ids[] = $cashbox->getId();
            $cashbox->setWorkplace($entity);
            $this->em->persist($cashbox);
        }

        if ($entity->getId()) {
            $qb = $this->em->getRepository('CommonBundle:Cashbox')->createQueryBuilder('c')
                ->update()
                ->set('c.workplace', 'NULL')
                ->andWhere('c.workplace = :workplace')->setParameter('workplace', $entity)
            ;
            if ($ids) {
                $qb->andWhere('c.id NOT IN (:ids)')->setParameter('ids', $ids);
            }

            $qb->getQuery()->execute();
        }

        return $entity;
    }

    public function getRenderExtraOptions($entity)
    {
        /** @var $entity \KreaLab\CommonBundle\Entity\Workplace */

        $operators = null;
        if ($entity->getId()) {
            $operators = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
                ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_OPERATOR%')
                ->andWhere('u.workplace = :workplace')->setParameter('workplace', $entity)
                ->addOrderBy('u.active', 'DESC')
                ->addOrderBy('u.last_name')
                ->addOrderBy('u.first_name')
                ->addOrderBy('u.patronymic')
                ->getQuery()->execute();
        }

        $legalEntities = $this->em->getRepository('CommonBundle:LegalEntity')->createQueryBuilder('le')
            ->andWhere('le.active = :leactive')->setParameter('leactive', true)
            ->leftJoin('le.cashboxes', 'c', 'WITH', 'c.active = 1')->addSelect('c')
            ->getQuery()->execute();
        $cashboxes     = [];
        foreach ($legalEntities as $legalEntity) { /** @var $legalEntity \KreaLab\CommonBundle\Entity\LegalEntity */
            $cashboxes[$legalEntity->getId()] = [];
            foreach ($legalEntity->getCashboxes() as $cashbox) {
                $cashboxes[$legalEntity->getId()][] = $cashbox->getId();
            }
        }

        return [
            'operators' => $operators,
            'cashboxes' => $cashboxes,
        ];
    }
}
