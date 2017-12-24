<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;

class EegController extends AbstractEntityController
{
    protected $tmplItem = 'AdminBundle:Eeg:item.html.twig';
    protected $tmplList = 'AdminBundle:Eeg:list.html.twig';
}
