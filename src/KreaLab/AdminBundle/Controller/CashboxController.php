<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;

class CashboxController extends AbstractEntityController
{
    protected $listFields = ['filial', 'workplace', 'legal_entity', 'num'];
    protected $orderBy    = ['active' => 'DESC'];
    protected $perms      = ['ROLE_MANAGE_FILIALS'];
    protected $routerList = 'admin_cashboxes';
    protected $tmplItem   = 'AdminBundle::_item.html.twig';
    protected $tmplList   = 'AdminBundle::_list.html.twig';

    public function listQb()
    {
        return parent::listQb()
            ->leftJoin('e.workplace', 'w')->addSelect('w')
            ->leftJoin('w.filial', 'f')->addSelect('f')
            ->addOrderBy('f.name')
            ->addOrderBy('w.name')
            ->addOrderBy('e.num')
        ;
    }
}
