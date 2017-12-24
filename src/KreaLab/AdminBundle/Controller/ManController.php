<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;

class ManController extends AbstractEntityController
{
    protected $tmplItem   = 'AdminBundle:Man:item.html.twig';
    protected $tmplList   = 'AdminBundle:Man:list.html.twig';
    protected $listFields = ['brigade', 'specialty', 'FullName'];
    protected $routerList = 'admin_men';
}
