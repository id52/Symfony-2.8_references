<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;

class LegalEntityController extends AbstractEntityController
{
    protected $listFields = ['name', ['inn', 'min_col']];
    protected $orderBy    = ['active' => 'DESC', 'name' => 'ASC'];
    protected $perms      = ['ROLE_MANAGE_FILIALS'];
    protected $routerList = 'admin_legal_entities';
    protected $tmplItem   = 'AdminBundle::_item.html.twig';
    protected $tmplList   = 'AdminBundle::_list.html.twig';
}
