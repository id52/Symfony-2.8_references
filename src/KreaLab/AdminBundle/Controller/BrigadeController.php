<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;

class BrigadeController extends AbstractEntityController
{
    protected $listFields = ['name', 'legal_entity'];
    protected $tmplItem   = 'AdminBundle:Brigade:item.html.twig';
    protected $tmplList   = 'AdminBundle:Brigade:list.html.twig';

    public function viewAction($id)
    {
        $entity = $this->repo->find($id);
        if (!$entity) {
            throw $this->createNotFoundException($this->entityName.' for id "'.$id.'" not found.');
        }

        return $this->render('AdminBundle:Brigade:view.html.twig', [
            'entity' => $entity,
        ]);
    }
}
