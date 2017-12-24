<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;

class ConsumableTagCategoryController extends AbstractEntityController
{
    protected $orderBy    = [
        'active'   => 'DESC',
        'position' => 'ASC',
    ];
    protected $routerList = 'admin_consumable_tag_categories';
    protected $tmplItem   = 'AdminBundle:ConsumableTagCategory:item.html.twig';
    protected $tmplList   = 'AdminBundle:ConsumableTagCategory:list.html.twig';
}
