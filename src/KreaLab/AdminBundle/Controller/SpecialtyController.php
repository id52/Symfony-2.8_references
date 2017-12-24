<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;

class SpecialtyController extends AbstractEntityController
{
    protected $tmplItem   = 'AdminBundle:Specialty:item.html.twig';
    protected $tmplList   = 'AdminBundle:Specialty:list.html.twig';
    protected $routerList = 'admin_specialties';
    protected $listFields = ['name', ['EegString', 'min_col']];
}
