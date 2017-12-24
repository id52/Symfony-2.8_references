<?php

namespace KreaLab\AppBundle\Controller;

use Doctrine\ORM\EntityRepository;
use KreaLab\CommonBundle\Entity\ActionLog;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class ArchivariusController extends Controller
{
    /** @var $em \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->denyAccessUnlessGranted('ROLE_ARCHIVARIUS');
    }

    /**
     * @Route("/archivarius-fio/", name="archivarius_fio")
     * @Template("AppBundle:Archivarius:form.html.twig")
     */
    public function fioAction(Request $request)
    {
        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('last_name', TextType::class, [
            'label'       => 'Фамилия',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('first_name', TextType::class, [
            'label'       => 'Имя',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('patronymic', TextType::class, [
            'label'       => 'Отчество',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('birthday', BirthdayType::class, [
            'label'       => 'Дата рождения',
            'constraints' => new Assert\NotBlank(),
            'years'       => range(1930, date('Y')),
        ]);
        $fb->add('submit', SubmitType::class, ['label' => 'Найти']);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $lastName   = $form->get('last_name')->getData();
            $firstName  = $form->get('first_name')->getData();
            $patronymic = $form->get('patronymic')->getData();
            $birthday   = $form->get('birthday')->getData()->format('Y-m-d');
            $qb         = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl');
            $qb->andWhere('sl.first_name = :first_name')->setParameter('first_name', $firstName);
            $qb->andWhere('sl.patronymic = :patronymic')->setParameter('patronymic', $patronymic);
            $qb->andWhere('sl.last_name = :last_name')->setParameter('last_name', $lastName);
            $qb->andWhere('sl.birthday = :birthday')->setParameter('birthday', $birthday);
            $logs = $qb->getQuery()->execute();

            if (count($logs) > 0) {
                $alog = new ActionLog();
                $alog->setActionType('archivarius_search');
                $alog->setUser($this->getUser());
                $alog->setParams([
                    'search_type' => 'fio',
                    'last_name'   => $lastName,
                    'first_name'  => $firstName,
                    'patronymic'  => $patronymic,
                    'birthday'    => $birthday,
                ]);
                $this->em->persist($alog);
                $this->em->flush();
            }

            if (count($logs) > 1) {
                return $this->render('AppBundle:Archivarius:list_fio.html.twig', [
                    'logs'       => $logs,
                    'first_name' => $firstName,
                    'patronymic' => $patronymic,
                    'last_name'  => $lastName,
                    'birthday'   => $birthday,
                ]);
            } elseif (count($logs) == 1) {
                /** @var $log \KreaLab\CommonBundle\Entity\ServiceLog */
                $log = $logs[0];
                return $this->redirectToRoute('archivarius_fio_view', ['id' => $log->getId()]);
            } else {
                $form->addError(new FormError('По заданным параметрам ничего не найдено.'));
            }
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/archivarius-date/", name="archivarius_date")
     * @Template("AppBundle:Archivarius:form.html.twig")
     */
    public function dateAction(Request $request)
    {
        $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT(b.serie) AS serie')
            ->andWhere('b.serie != :serie')->setParameter('serie', '')
            ->andWhere('b.status = :status')->setParameter('status', 'usedByOperator')
            ->orderBy('serie')
            ->getQuery()->execute();

        $seriesChoices['- Выберите серию -'] = '';
        foreach ($series as $serie) {
            $seriesChoices[$serie['serie']] = $serie['serie'];
        }

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'choices'           => $seriesChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);
        $fb->add('num_blank', TextType::class, [
            'label'       => 'Номер бланка',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('date_giving', DateType::class, [
            'label'       => 'Дата выдачи',
            'constraints' => new Assert\NotBlank(),
            'years'       => range(1930, date('Y')),
        ]);
        $fb->add('submit', SubmitType::class, ['label' => 'Найти']);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $numBlank   = $form->get('num_blank')->getData();
            $dateGiving = $form->get('date_giving')->getData()->format('Y-m-d');

            $qb = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
                ->leftJoin('sl.blank', 'b')->addSelect('b')
                ->andWhere('sl.num_blank = :num_blank')->setParameter('num_blank', (int)$numBlank)
                ->andWhere('sl.date_giving = :date_giving')
                ->andWhere('b.leading_zeros = :leading_zeros')->setParameter('leading_zeros', strlen($numBlank))
                ->setParameter('date_giving', $dateGiving);

            if (!empty($form->get('serie')->getData())) {
                $qb->andWhere('b.serie = :serie')->setParameter('serie', $form->get('serie')->getData());
            }

            $logs = $qb->getQuery()->execute();

            if (count($logs) > 0) {
                $alog = new ActionLog();
                $alog->setActionType('archivarius_search');
                $alog->setUser($this->getUser());
                $alog->setParams([
                    'search_type' => 'date',
                    'num_blank'   => $numBlank,
                    'date_giving' => $dateGiving,
                ]);
                $this->em->persist($alog);
                $this->em->flush();
            }

            if (count($logs) > 1) {
                return $this->render('AppBundle:Archivarius:list.html.twig', ['logs' => $logs]);
            } elseif (count($logs) == 1) {
                /** @var $log \KreaLab\CommonBundle\Entity\ServiceLog */
                $log = $logs[0];
                return $this->redirectToRoute('archivarius_date_view', ['id' => $log->getId()]);
            } else {
                $form->addError(new FormError('По заданным параметрам ничего не найдено.'));
            }
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/archivarius-legal/", name="archivarius_legal")
     * @Template("AppBundle:Archivarius:form.html.twig")
     */
    public function legalAction(Request $request)
    {
        $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT(b.serie) AS serie')
            ->andWhere('b.serie != :serie')->setParameter('serie', '')
            ->andWhere('b.status = :status')->setParameter('status', 'usedByOperator')
            ->orderBy('serie')
            ->getQuery()->execute();

        $seriesChoices['- Выберите серию -'] = '';
        foreach ($series as $serie) {
            $seriesChoices[$serie['serie']] = $serie['serie'];
        }

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'choices'           => $seriesChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);
        $fb->add('num_blank', TextType::class, [
            'label'       => 'Номер бланка',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('legal_entity', EntityType::class, [
            'label'         => 'Юридическое лицо',
            'class'         => 'CommonBundle:LegalEntity',
            'placeholder'   => ' - Выберите юридическое лицо - ',
            'choice_label'  => 'nameAndShortName',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('le')
                    ->andWhere('le.active = :active')->setParameter('active', true)
                    ->addOrderBy('le.name');
            },
        ]);
        $fb->add('submit', SubmitType::class, ['label' => 'Найти']);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $numBlank    = $form->get('num_blank')->getData();
            $legalEntity = $form->get('legal_entity')->getData();

            $qb = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
                ->leftJoin('sl.blank', 'b')->addSelect('b')
                ->andWhere('sl.num_blank = :num_blank')->setParameter('num_blank', (int)$numBlank)
                ->leftJoin('sl.workplace', 'w')
                ->andWhere('w.legal_entity = :legal_entity')
                ->setParameter('legal_entity', $legalEntity)
                ->andWhere('b.leading_zeros = :leading_zeros')
                ->setParameter('leading_zeros', strlen($numBlank));

            if (!empty($form->get('serie')->getData())) {
                $qb->andWhere('b.serie = :serie')->setParameter('serie', $form->get('serie')->getData());
            }

            $logs = $qb->getQuery()->execute();

            if (count($logs) > 0) {
                $alog = new ActionLog();
                $alog->setActionType('archivarius_search');
                $alog->setUser($this->getUser());
                $alog->setParams([
                    'search_type'  => 'legal',
                    'num_blank'    => $numBlank,
                    'legal_entity' => $legalEntity->getId(),
                ]);
                $this->em->persist($alog);
                $this->em->flush();
            }

            if (count($logs) > 1) {
                return $this->render('AppBundle:Archivarius:list.html.twig', [
                    'logs'         => $logs,
                    'legal_entity' => $legalEntity,
                    'num_blank'    => $numBlank,
                ]);
            } elseif (count($logs) == 1) {
                /** @var $log \KreaLab\CommonBundle\Entity\ServiceLog */
                $log = $logs[0];
                return $this->redirectToRoute('archivarius_legal_view', ['id' => $log->getId()]);
            } else {
                $form->addError(new FormError('По заданным параметрам ничего не найдено.'));
            }
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/archivarius-number/view-{id}/", name="archivarius_number_view", requirements={"id": "\d+"})
     * @Route("/archivarius-fio/view-{id}/", name="archivarius_fio_view", requirements={"id": "\d+"})
     * @Route("/archivarius-date/view-{id}/", name="archivarius_date_view", requirements={"id": "\d+"})
     * @Route("/archivarius-legal/view-{id}/", name="archivarius_legal_view", requirements={"id": "\d+"})
     * @Template("AppBundle:Archivarius:view.html.twig")
     */
    public function viewAction($id)
    {
        $log = $this->em->find('CommonBundle:ServiceLog', $id);
        if (!$log) {
            throw $this->createNotFoundException();
        }

        $alog = new ActionLog();
        $alog->setActionType('archivarius_view');
        $alog->setUser($this->getUser());
        $alog->setParams(['log' => $log->getId()]);
        $this->em->persist($alog);
        $this->em->flush();

        $sumType  = $log->getParams()['sum_type'];
        $discount = $this->em->find('CommonBundle:Discount', $sumType);

        return [
            'log'      => $log,
            'discount' => $discount,
        ];
    }

    /**
     * @Route("/archivarius-pdf-blank-{id}/", name="archivarius_pdf_blank", requirements={"id": "\d+"})
     */
    public function pdfAction($id)
    {
        $log = $this->em->find('CommonBundle:ServiceLog', $id);
        if (!$log) {
            throw $this->createNotFoundException();
        }

        $alog = new ActionLog();
        $alog->setActionType('archivarius_pdf');
        $alog->setUser($this->getUser());
        $alog->setParams(['log' => $log->getId()]);
        $this->em->persist($alog);
        $this->em->flush();

        $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
        $params    = $log->getParams();
        $file      = $this->get('kernel')->locateResource('@AppBundle').'Resources/pdf/certificate.pdf';
        $pff       = $this->get('pdf_form_filler');
        $pdf       = $pff->fill($file, [
            'number'                 => '№ '.$log->getId(),
            'Surname'                => $params['last_name'],
            'First_name_Second_name' => $params['first_name'].' '.$params['patronymic'],
            'City'                   => 'г. Москва',
            'Date'                   => $formatter->format(new \DateTime()),
        ]);

        $response = new BinaryFileResponse($pdf);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="blank.pdf"');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * @Route("/archivarius-number/", name="archivarius_number")
     * @Template("AppBundle:Archivarius:form.html.twig")
     */
    public function numberAction(Request $request)
    {
        $series = $this->em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->select('DISTINCT(b.serie) AS serie')
            ->andWhere('b.serie != :serie')->setParameter('serie', '')
            ->andWhere('b.status IN (:statuses)')->setParameter('statuses', [
                'usedByOperator',
                'replacedBecauseNoStampByOperator',
            ])
            ->orderBy('serie')
            ->getQuery()->execute();

        $seriesChoices['- Выберите серию -'] = '';
        foreach ($series as $serie) {
            $seriesChoices[$serie['serie']] = $serie['serie'];
        }

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('serie', ChoiceType::class, [
            'label'             => 'Серия',
            'choices'           => $seriesChoices,
            'required'          => false,
            'choices_as_values' => true,
        ]);
        $fb->add('num_blank', TextType::class, [
            'label'       => 'Номер бланка',
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('submit', SubmitType::class, ['label' => 'Найти']);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $numBlank = $form->get('num_blank')->getData();

            $qb = $this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
                ->leftJoin('sl.blank', 'b')->addSelect('b')
                ->andWhere('sl.num_blank LIKE :num_blank')->setParameter('num_blank', '%'.(int)$numBlank.'%')
                ->andWhere('b.leading_zeros = :leading_zeros')->setParameter('leading_zeros', strlen($numBlank))
                ->leftJoin('sl.workplace', 'w');

            if (!empty($form->get('serie')->getData())) {
                $qb->andWhere('b.serie = :serie')->setParameter('serie', $form->get('serie')->getData());
            }

            $logs = $qb->getQuery()->execute();

            if (count($logs) > 0) {
                $alog = new ActionLog();
                $alog->setActionType('archivarius_search');
                $alog->setUser($this->getUser());
                $alog->setParams([
                    'search_type' => 'legal',
                    'num_blank'   => $numBlank,
                ]);
                $this->em->persist($alog);
                $this->em->flush();
            }

            if (count($logs) > 1) {
                return $this->render('AppBundle:Archivarius:list.html.twig', [
                    'logs'      => $logs,
                    'num_blank' => $numBlank,
                ]);
            } elseif (count($logs) == 1) {
                /** @var $log \KreaLab\CommonBundle\Entity\ServiceLog */
                $log = $logs[0];
                return $this->redirectToRoute('archivarius_number_view', ['id' => $log->getId()]);
            } else {
                $form->addError(new FormError('По заданным параметрам ничего не найдено.'));
            }
        }

        return ['form' => $form->createView()];
    }
}
