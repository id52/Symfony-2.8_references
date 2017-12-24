<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;

class OrderTypeController extends AbstractEntityController
{
    protected $tmplItem = 'AdminBundle:OrderType:item.html.twig';
    protected $tmplList = 'AdminBundle:OrderType:list.html.twig';
}
