<?php

namespace KreaLab\AppBundle\Controller;

use KreaLab\CommonBundle\Entity\OperatorReplacementLog;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Component\Form\FormError;
use KreaLab\CommonBundle\Entity\ActionLog;

class ReplacerController extends Controller
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->denyAccessUnlessGranted('ROLE_REPLACER');
        $this->em = $this->get('doctrine.orm.entity_manager');
    }

    /** @Route("/replacements/add/", name="replacer__replacement__add") */
    public function addAction(Request $request)
    {
        $successors = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
            ->andWhere('u.active = :active')->setParameter('active', true)
            ->andWhere('u.roles LIKE :role_operator')
            ->andWhere('u.successor IS NULL')
            ->leftJoin('u.predecessor', 'p')->addSelect('p')
            ->andWhere('p.id IS NULL')
            ->setParameter('role_operator', '%ROLE_OPERATOR%')
            ->getQuery()->getResult();

        $userChoices = [];
        foreach ($successors as $successor) { /** @var $successor \KreaLab\CommonBundle\Entity\User */
            $userChoices[$successor->getFullName()] = $successor->getId();
        }

        $fb = $this->createFormBuilder([], [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('predecessor', ChoiceType::class, [
            'required'          => false,
            'label'             => 'Заменяемый оператор',
            'choices'           => $userChoices,
            'choices_as_values' => true,
            'placeholder'       => '-- Выберите заменяемого оператора --',
        ]);
        $fb->add('successor', ChoiceType::class, [
            'required'          => false,
            'label'             => 'Заменяющий оператор',
            'choices'           => $userChoices,
            'choices_as_values' => true,
            'placeholder'       => '-- Выберите заменяющего оператора --',
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);

        if ($request->isMethod('post')) {
            $predecessor = $this->em->find('CommonBundle:User', $form->get('predecessor')->getData());

            if (!$predecessor) {
                throw $this->createNotFoundException('Нет предшественника');
            }

            $successor = $this->em->find('CommonBundle:User', $form->get('successor')->getData());
            if (!$successor) {
                throw $this->createNotFoundException('Нет приемника');
            }

            if ($successor == $predecessor) {
                $form->get('successor')->addError(new FormError('Заменяемый оператор не должен быть заменяющим'));
            }

            if ($form->isValid()) {
                $sessions = $predecessor->getSessions();
                foreach ($sessions as $session) { /** @var $session \KreaLab\CommonBundle\Entity\Session */
                    $this->em->remove($session);
                }

                $sessions = $successor->getSessions();
                foreach ($sessions as $session) {  /** @var $session \KreaLab\CommonBundle\Entity\Session */
                    $this->em->remove($session);
                }

                $predecessor->setSuccessor($successor);
                $this->em->persist($predecessor);
                $successor->setPredecessor($predecessor);
                $this->em->persist($successor);

                $this->em->flush();

                if ($predecessor->getSessions()) {
                    $filials = $predecessor->getFilials();
                    $filial  = $filials[0];

                    /** @var $shiftLog \KreaLab\CommonBundle\Entity\ShiftLog */
                    $shiftLog = $this->em->getRepository('CommonBundle:ShiftLog')->createQueryBuilder('s')
                        ->andWhere('s.user = :user')->setParameter('user', $predecessor)
                        ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
                        ->andWhere('s.date = :date')->setParameter('date', new \DateTime('today'))
                        ->andWhere('s.endTime IS NULL')->setMaxResults(1)
                        ->getQuery()->getOneOrNullResult();

                    if ($shiftLog) {
                        $shiftLog->setEndTime(new \DateTime());
                        $this->em->persist($shiftLog);
                        $this->em->flush();
                    }
                }

                $log = new ActionLog();
                $log->setUser($predecessor);
                $log->setActionType('logout');
                $log->setParams([
                    'ip'     => $request->getClientIp(),
                    'reason' => 'logout_by_Replacer__replacement_added',
                ]);
                $this->em->persist($log);

                $log = new ActionLog();
                $log->setUser($successor);
                $log->setActionType('logout');
                $log->setParams([
                    'ip'     => $request->getClientIp(),
                    'reason' => 'logout_by_Replacer__replacement_added',
                ]);
                $this->em->persist($log);

                $this->em->flush();

                $operatorReplacementLog = new OperatorReplacementLog();
                $operatorReplacementLog->setPredecessor($predecessor);
                $operatorReplacementLog->setSuccessor($successor);
                $this->em->persist($operatorReplacementLog);

                $this->em->flush();

                $this->addFlash('success', 'Установили заменяющего оператора');
                return $this->redirectToRoute('replacer__replacements__list');
            }
        }

        return $this->render('AppBundle:Replacer:add.html.twig', [
            'form'        => $form->createView(),
        ]);
    }

    /** @Route("/replacements/delete-{id}/", name="replacer__replacement__delete") */
    public function deleteAction(Request $request, $id)
    {
        /** @var $predecessor \KreaLab\CommonBundle\Entity\User */
        $predecessor = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
            ->andWhere('u.active = :active')->setParameter('active', true)
            ->andWhere('u.roles LIKE :role_operator')
            ->setParameter('role_operator', '%ROLE_OPERATOR%')
            ->andWhere('u.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();

        if (!$predecessor) {
            throw $this->createNotFoundException('Нет предшественника');
        }

        $successor = $predecessor->getSuccessor(); /** @var $successor \KreaLab\CommonBundle\Entity\User */

        $sessions = $successor->getSessions();
        foreach ($sessions as $session) {  /** @var $session \KreaLab\CommonBundle\Entity\Session */
            $this->em->remove($session);
        }

        $sessions = $predecessor->getSessions();
        foreach ($sessions as $session) { /** @var $session \KreaLab\CommonBundle\Entity\Session */
            $this->em->remove($session);
        }

        $predecessor->setSuccessor(null);
        $this->em->persist($predecessor);

        $successor->setPredecessor(null);
        $this->em->persist($successor);

        $this->em->flush();

        $filials = $predecessor->getFilials();
        $filial  = $filials[0];

        if ($successor->getSessions()) {
            foreach ($successor->getSessions() as $session) {
                /** @var $session \Symfony\Component\HttpFoundation\Session\Session */

                $session->invalidate();
            }

            /** @var $shiftLog \KreaLab\CommonBundle\Entity\ShiftLog */
            $shiftLog = $this->em->getRepository('CommonBundle:ShiftLog')->createQueryBuilder('s')
                ->andWhere('s.user = :user')->setParameter('user', $successor)
                ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
                ->andWhere('s.date = :date')->setParameter('date', new \DateTime('today'))
                ->andWhere('s.endTime IS NULL')->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();

            if ($shiftLog) {
                $shiftLog->setEndTime(new \DateTime());
                $this->em->persist($shiftLog);
                $this->em->flush();
            }
        }

        $log = new ActionLog();
        $log->setUser($predecessor);
        $log->setActionType('logout');
        $log->setParams([
            'ip'     => $request->getClientIp(),
            'reason' => 'logout_by_Replacer__replacement_deleted',
        ]);
        $this->em->persist($log);

        $log = new ActionLog();
        $log->setUser($successor);
        $log->setActionType('logout');
        $log->setParams([
            'ip'     => $request->getClientIp(),
            'reason' => 'logout_by_Replacer__replacement_deleted',
        ]);
        $this->em->persist($log);

        $this->em->flush();

        $operatorReplacementLog = $this->em->getRepository('CommonBundle:OperatorReplacementLog')->findOneBy([
            'successor'   => $successor,
            'predecessor' => $predecessor,
            'removed_at'  => null,
        ]);

        if (!$operatorReplacementLog) {
            throw $this->createNotFoundException('Нет лога замены');
        }

        $operatorReplacementLog->setRemovedAt(new \DateTime());
        $this->em->persist($operatorReplacementLog);

        $this->em->flush();

        $this->addFlash('success', 'Удалили');
        return $this->redirectToRoute('replacer__replacements__list');
    }

    /** @Route("/replacements/", name="replacer__replacements__list") */
    public function listAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
            ->andWhere('u.active = :active')->setParameter('active', true)
            ->andWhere('u.roles LIKE :role_operator')
            ->andWhere('u.successor IS NOT NULL')
            ->setParameter('role_operator', '%ROLE_OPERATOR%')
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Replacer:list.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    /** @Route("/replacements/logs/", name="replacer__logs__list") */
    public function logAction(Request $request)
    {
        $qb = $this->em->getRepository('CommonBundle:OperatorReplacementLog')->createQueryBuilder('orl')
            ->orderBy('orl.id', 'DESC')
        ;

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->get('page', 1));

        return $this->render('AppBundle:Replacer:logs.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }
}
