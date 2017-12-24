<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ConsumableTagController extends AbstractEntityController
{
    protected $tmplItem = 'AdminBundle:ConsumableTag:item.html.twig';
    protected $tmplList = 'AdminBundle:ConsumableTag:list.html.twig';
    /** @var \KreaLab\CommonBundle\Entity\ConsumableTagCategory */
    protected $category;
    /** @var \Symfony\Component\HttpFoundation\Request */
    protected $request;

    public function init()
    {
        parent::init();
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->request = $this->container->get('request_stack')->getCurrentRequest();

        if ($this->request->get('tag_category')) {
            $tagCategory = $this->em->find('CommonBundle:ConsumableTagCategory', $this->request->get('tag_category'));
            if (!$tagCategory) {
                throw new HttpException(404);
            }

            $this->category = $tagCategory->getId();
        } elseif ($this->request->get('id')) {
            /** @var $tag \KreaLab\CommonBundle\Entity\ConsumableTag */
            $tag = $this->em->getRepository('CommonBundle:ConsumableTag')->find($this->request->get('id'));
            if (!$tag) {
                throw new HttpException(404);
            }

            $this->category = $tag->getTagCategory()->getId();
        }

        $twig = $this->container->get('twig');
        $twig->addGlobal('tag_category', $this->category);
    }

    public function redirectToList()
    {
        return $this->redirectToRoute($this->routerList, ['tag_category' => $this->category]);
    }

    public function redirectToAdd()
    {
        return $this->redirectToRoute($this->routerItemAdd, ['tag_category' => $this->category]);
    }

    protected function listQb()
    {
        $qb = parent::listQb();
        $qb->andWhere('e.tag_category = :category')->setParameter('category', $this->category);
        return $qb;
    }

    /**
     * @param $entity \KreaLab\CommonBundle\Entity\ConsumableTag
     * @return \KreaLab\CommonBundle\Entity\ConsumableTag
     */
    protected function prePersist($entity)
    {
        $tagCategory = intval($this->request->get('tag_category'));
        $entity->setTagCategory($this->em->getRepository('CommonBundle:ConsumableTagCategory')->find($tagCategory));
        return $entity;
    }
}
