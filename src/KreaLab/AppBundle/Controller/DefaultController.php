<?php

namespace KreaLab\AppBundle\Controller;

use KreaLab\CommonBundle\Entity\FilialBanLog;
use KreaLab\CommonBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    public function init()
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
    }

    /** @Route("/", name="homepage") */
    public function indexAction()
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_settings');
        }

        if ($this->isGranted('ROLE_MANAGE_FILIALS')) {
            return $this->redirectToRoute('admin_filials');
        }

        if ($this->isGranted('ROLE_MANAGE_WORKERS')) {
            return $this->redirectToRoute('admin_users');
        }

        if ($this->isGranted('ROLE_ARCHIVARIUS')) {
            return $this->redirectToRoute('archivarius_fio');
        }

        if ($this->isGranted('ROLE_CASHIER')) {
            return $this->redirectToRoute('cashier_get');
        }

        if ($this->isGranted('ROLE_SUPERVISOR')) {
            return $this->redirectToRoute('supervisor_get_evelopes');
        }

        if ($this->isGranted('ROLE_SENIOR_OPERATOR')) {
            return $this->redirectToRoute('senior_current');
        }

        if ($this->isGranted('ROLE_OPERATOR')) {
            return $this->redirectToRoute('services');
        }

        if ($this->isGranted('ROLE_ORDERMAN')) {
            return $this->redirectToRoute('orderman_orders');
        }

        if ($this->isGranted('ROLE_COURIER')) {
            return $this->redirectToRoute('courier_get_evelopes');
        }

        if ($this->isGranted('ROLE_ACQUITTANCEMAN')) {
            return $this->redirectToRoute('acquittanceman_orders');
        }

        if ($this->isGranted('ROLE_TREASURER')) {
            return $this->redirectToRoute('treasurer_orders');
        }

        if ($this->isGranted('ROLE_SUPERVISOR')) {
            return $this->redirectToRoute('supervisor_orders');
        }

        if ($this->isGranted('ROLE_STOCKMAN')) {
            return $this->redirectToRoute('stockman_blanks_instock');
        }

        if ($this->isGranted('ROLE_REFERENCEMAN')) {
            return $this->redirectToRoute('referenceman_blanks_stockman_envelopes');
        }

        if ($this->isGranted('ROLE_REPLACER')) {
            return $this->redirectToRoute('replacer__replacements__list');
        }

        throw $this->createAccessDeniedException();
    }

    /**
     * @Route("/ban/", name="ban")
     */
    public function banAction(Request $request)
    {
        $clientIps = $request->getClientIps();

        $banIps     = [];
        $banFilials = [];
        $filials    = $this->em->getRepository('CommonBundle:Filial')->createQueryBuilder('f')
            ->andWhere('f.active = :active')->setParameter('active', true)
            ->getQuery()->execute();
        foreach ($filials as $filial) { /** @var $filial \KreaLab\CommonBundle\Entity\Filial */
            foreach ($filial->getIps() as $ip) {
                if (in_array($ip, $clientIps)) {
                    $banFilials[] = $filial;
                    foreach ($filial->getIps() as $banIp) {
                        $banIps[] = $banIp;
                    }
                }
            }
        }

        $banIps = array_unique($banIps);

        if ($banIps) {
            $this->em->getRepository('CommonBundle:Session')->createQueryBuilder('s')
                ->delete()
                ->andWhere('s.ip IN (:ips)')->setParameter('ips', $banIps)
                ->getQuery()->execute();
        }

        if ($banFilials) {
            $admins = $this->em->getRepository('CommonBundle:User')->createQueryBuilder('u')
                ->andWhere('u.active = :active')->setParameter('active', true)
                ->andWhere('u.roles LIKE :role')->setParameter('role', '%ROLE_ADMIN%')
                ->andWhere('u.phone IS NOT NULL')
                ->getQuery()->execute();

            foreach ($banFilials as $filial) { /** @var $filial \KreaLab\CommonBundle\Entity\Filial */
                $filial->setActive(false);
                $this->em->persist($filial);

                $log = new FilialBanLog();
                $log->setUser($this->getUser());
                $log->setFilial($filial);
                $this->em->persist($log);

                $sms = $this->getUser()->getFullName().' заблокировал филиал '.$filial->getName();
                foreach ($admins as $admin) { /** @var $admin \KreaLab\CommonBundle\Entity\User */
                    $this->get('sms_uslugi_ru')->send($admin->getPhone(), $sms);
                }

                $this->em->flush();
            }
        }

        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();
        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/balance/", name="balance")
     * @Template
     */
    public function balanceAction()
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_SUPERVISOR']);

        $filials = $this->em->getRepository('CommonBundle:Filial')->createQueryBuilder('f')
            ->leftJoin('f.workplaces', 'w')->addSelect('w')
            ->leftJoin('w.envelopes', 'e', 'WITH', 'e.courier IS NULL')
            ->addSelect('SUM(e.sum) as e_sum')
            ->addGroupBy('w.id')
            ->addOrderBy('f.name')
            ->addOrderBy('w.name')
            ->getQuery()->getArrayResult();

        foreach ($filials as $filial) {
            $filialsArr[$filial[0]['id']]          = $filial[0];
            $filialsArr[$filial[0]['id']]['e_sum'] = $filial['e_sum'];

            if (!isset($filialsArr[$filial[0]['id']]['sum'])) {
                $filialsArr[$filial[0]['id']]['sum'] = 0;
            }

            if (!isset($filialsArr[$filial[0]['id']]['sum_old'])) {
                $filialsArr[$filial[0]['id']]['sum_old'] = 0;
            }

            foreach ($filialsArr[$filial[0]['id']]['workplaces'] as &$workplace) {
                $workplace['sum_old']
                    = (int)$this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
                        ->select('SUM(sl.sum)')
                        ->andWhere('DATE(sl.created_at) < :today')->setParameter('today', new \DateTime('today'))
                        ->andWhere('sl.workplace = :workplace')->setParameter('workplace', $workplace['id'])
                        ->andWhere('sl.envelope IS NULL')
                        ->andWhere('sl.import = 0')
                        ->getQuery()->getSingleScalarResult();

                $workplace['sum']
                    = (int)$this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
                        ->select('SUM(sl.sum)')
                        ->andWhere('DATE(sl.created_at) = :today')->setParameter('today', new \DateTime('today'))
                        ->andWhere('sl.workplace = :workplace')->setParameter('workplace', $workplace['id'])
                        ->andWhere('sl.envelope IS NULL')
                        ->andWhere('sl.import = 0')
                        ->getQuery()->getSingleScalarResult();

                $workplace['e_sum']
                    = (int)$this->em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('sl')
                        ->select('SUM(sl.sum)')
                        ->andWhere('sl.workplace = :workplace')->setParameter('workplace', $workplace['id'])
                        ->andWhere('sl.envelope IS NOT NULL')
                        ->andWhere('sl.import = 0')
                        ->getQuery()->getSingleScalarResult();

                $filialsArr[$filial[0]['id']]['sum_old'] += $workplace['sum_old'];
                $filialsArr[$filial[0]['id']]['sum']     += $workplace['sum'];
                $filialsArr[$filial[0]['id']]['e_sum']   += $workplace['e_sum'];
            }
        }

        return [
            'filials' => $filialsArr,
        ];
    }

    /**
     * @Route("/force-change-pass/", name="force_change_pass")
     * @Template("AppBundle:Default:force_change_pass.html.twig")
     */
    public function forceChangePassAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user || !$user instanceof User || !$user->getForceChangePass()) {
            return $this->createNotFoundException();
        }

        $fb = $this->createFormBuilder(null, ['translation_domain' => false]);
        $fb->add('password', RepeatedType::class, [
            'type'            => PasswordType::class,
            'first_options'   => ['label' => 'Пароль'],
            'second_options'  => ['label' => 'Повтор пароля'],
            'invalid_message' => 'passwords_not_equals',
        ]);
        $fb->add('submit', SubmitType::class, ['label' => 'Сменить']);
        $fb->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $pass = trim($form->get('password')->get('first')->getData());

            if (iconv_strlen($pass) < 8) {
                $error = new FormError('Количество символов должно быть не менее 8.');
                $form->get('password')->get('first')->addError($error);
            }

            if (!preg_match('/^[A-z0-9]+$/', $pass)) {
                $error = new FormError('Должны использоваться только латинские буквы (A-z) и цифры (0-9).');
                $form->get('password')->get('first')->addError($error);
                return;
            }

            if (iconv_strlen(preg_replace('/[^A-Z]/', '', $pass)) != 1) {
                $error = new FormError('Должна быть одна заглавная буква.');
                $form->get('password')->get('first')->addError($error);
            }

            if (iconv_strlen(preg_replace('/[^a-z]/', '', $pass)) < 3) {
                $error = new FormError('Строчных букв должно быть не менее трех.');
                $form->get('password')->get('first')->addError($error);
            }

            $passLength = iconv_strlen($pass);
            for ($i = 0; $i < $passLength; $i ++) {
                if (substr_count($pass, $pass[$i]) > 3) {
                    $error = new FormError('Символы не должны повторяться более трех раз.');
                    $form->get('password')->get('first')->addError($error);
                    break;
                }
            }

            if ($pass == 'Lenin1917') {
                $error = new FormError('Ваш пароль не должен совпадать с примером пароля.');
                $form->get('password')->get('first')->addError($error);
            }
        });
        $fb->setAction($this->generateUrl('force_change_pass'));
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var $user \KreaLab\CommonBundle\Entity\User */
            $user     = $this->getUser();
            $password = $form->get('password')->getData();
            $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $password));
            $user->setForceChangePass(false);

            $this->em->persist($user);
            $this->em->flush();

            return $this->redirectToRoute('homepage');
        }

        return ['form' => $form->createView()];
    }
}
