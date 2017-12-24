<?php

namespace KreaLab\CommonBundle\Repository;

use Doctrine\ORM\EntityRepository;
use KreaLab\CommonBundle\Entity\User;

class ActionLogRepository extends EntityRepository
{
    public function addParams(User $user, $params)
    {
        $log = $this->createQueryBuilder('al')
            ->andWhere('al.user = :user')->setParameter('user', $user)
            ->addOrderBy('al.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
        if ($log) {
            $log->setParams(array_merge($log->getParams(), $params));
            $this->_em->persist($log);
            $this->_em->flush();
        }
    }
}
