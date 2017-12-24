<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;

class ReferenceTypeController extends AbstractEntityController
{
    protected $blanksCnt = 0;
    protected $tmplItem  = 'AdminBundle:ReferenceType:item.html.twig';
    protected $tmplList  = 'AdminBundle:ReferenceType:list.html.twig';

    public function deleteAction($id)
    {
        /** @var  $entity \KreaLab\CommonBundle\Entity\ReferenceType */
        $entity = $this->repo->find($id);
        if (!$entity) {
            throw $this->createNotFoundException($this->entityName.' for id "'.$id.'" not found.');
        }

        $blanksCnt = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->andWhere('b.reference_type = :reference_type')->setParameter('reference_type', $entity)
            ->getQuery()->getSingleScalarResult();

        if ($blanksCnt) {
            throw $this->createNotFoundException('There are blanks.');
        }

        $this->em->remove($entity);
        $this->em->flush();

        $this->addFlash('success', $this->get('translator')->trans('flashes.success_deleted'));
        return $this->redirectToList();
    }

    /**
     * @param $entity \KreaLab\CommonBundle\Entity\ReferenceType
     */
    public function preForm($entity)
    {
        if ($entity->getId()) {
            $this->blanksCnt = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
                ->select('COUNT(b.id)')
                ->andWhere('b.reference_type = :reference_type')->setParameter('reference_type', $entity)
                ->getQuery()->getSingleScalarResult();
        }

        return $entity;
    }

    public function getFormOptions()
    {
        return [
            'translation_domain' => $this->entityNameS,
            'blanks_cnt'         => $this->blanksCnt,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRenderExtraOptions($entity)
    {
        return ['blanks_cnt' => $this->blanksCnt];
    }
}
