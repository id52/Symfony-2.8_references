<?php

namespace KreaLab\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class CourierController extends Controller
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
        $this->denyAccessUnlessGranted('ROLE_COURIER');
    }

    /**
     * @Route("/courier-get-evelopes/", name="courier_get_evelopes")
     * @Template("AppBundle:Courier:get_evelopes.html.twig")
     */
    public function getEvelopesAction(Request $request)
    {
        $id = $request->get('id', 0);
        if (!$id) {
            $filials = $this->em->getRepository('CommonBundle:Filial')->createQueryBuilder('f')
                ->andWhere('f.active = :active')->setParameter('active', true)
                ->andWhere(':user MEMBER OF f.users')->setParameter('user', $this->getUser())
                ->addOrderBy('f.name')
                ->leftJoin('f.workplaces', 'w')
                ->leftJoin('w.envelopes', 'e', 'WITH', 'e.courier IS NULL')
                ->addSelect('COUNT(e.id) AS e_cnt')
                ->addSelect('SUM(e.sum) AS e_sum')
                ->addGroupBy('f.id')
                ->getQuery()->execute();
            return $this->render('AppBundle:Courier:get_evelopes_list.html.twig', [
                'filials' => $filials,
            ]);
        }

        $filial = $this->em->getRepository('CommonBundle:Filial')->createQueryBuilder('f')
            ->andWhere('f.active = :active')->setParameter('active', true)
            ->andWhere(':user MEMBER OF f.users')->setParameter('user', $this->getUser())
            ->andWhere('f.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        $envelopes = [];
        if ($filial) {
            $envelopes = $this->em->getRepository('CommonBundle:Envelope')->createQueryBuilder('e')
                ->leftJoin('e.workplace', 'w')->addSelect('w')
                ->andWhere('w.filial = :filial')->setParameter('filial', $filial)
                ->andWhere('e.courier IS NULL')
                ->addOrderBy('w.name')
                ->getQuery()->execute();
        }

        $choices = [];
        foreach ($envelopes as $envelope) { /** @var $envelope \KreaLab\CommonBundle\Entity\Envelope */
            $choices[$envelope->getId()] = $envelope->getId();
        }

        $fb = $this->createFormBuilder(null, [
            'translation_domain' => false,
        ]);
        $fb->add('envelopes', ChoiceType::class, [
            'choices_as_values' => true,
            'multiple'          => true,
            'expanded'          => true,
            'choices'           => $choices,
            'constraints'       => new Assert\NotBlank(['message' => 'blank_evelopes']),
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $envelopes = $this->em->getRepository('CommonBundle:Envelope')->findBy([
                'id' => $form->get('envelopes')->getData(),
            ]);
            foreach ($envelopes as $envelope) { /** @var $envelope \KreaLab\CommonBundle\Entity\Envelope */
                $envelope->setCourier($this->getUser());
                $envelope->setCourierDatetime(new \DateTime());
                $this->em->persist($envelope);
            }

            $this->em->flush();
            return $this->redirectToRoute('courier_on_hands');
        }

        return [
            'form'      => $form->createView(),
            'envelopes' => $envelopes,
        ];
    }

    /**
     * @Route("/courier-on-hands/", name="courier_on_hands")
     * @Template("AppBundle:Courier:on_hands.html.twig")
     */
    public function onHandsAction()
    {
        $envelopes = $this->em->getRepository('CommonBundle:Envelope')->createQueryBuilder('e')
            ->leftJoin('e.workplace', 'w')->addSelect('w')
            ->leftJoin('w.filial', 'f')->addSelect('f')
            ->andWhere('e.courier = :courier')->setParameter('courier', $this->getUser())
            ->andWhere('e.supervisor IS NULL')
            ->addOrderBy('f.name')
            ->addOrderBy('w.name')
            ->getQuery()->execute();
        return ['envelopes' => $envelopes];
    }
}
