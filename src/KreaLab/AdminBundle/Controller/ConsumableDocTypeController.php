<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;

class ConsumableDocTypeController extends AbstractEntityController
{
    protected $tmplItem = 'AdminBundle:ConsumableDocType:item.html.twig';
    protected $tmplList = 'AdminBundle:ConsumableDocType:list.html.twig';
}
