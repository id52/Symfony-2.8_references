<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;

class CategoryController extends AbstractEntityController
{
    protected $orderBy    = ['name' => 'ASC'];
    protected $routerList = 'admin_categories';
    protected $tmplItem   = 'AdminBundle::_item.html.twig';
    protected $tmplList   = 'AdminBundle:Category:list.html.twig';
}
