<?php

namespace KreaLab\CommonBundle\Listeners;

use Doctrine\ORM\EntityManager;
use KreaLab\CommonBundle\Entity\ActionLog;
use KreaLab\CommonBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class Login implements AuthenticationSuccessHandlerInterface
{
    protected $em;
    protected $router;

    public function __construct(EntityManager $em, RouterInterface $router)
    {
        $this->em     = $em;
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();
        if ($user instanceof User) {
            if ($user->isOperator()) {
                $sessions = $this->em->getRepository('CommonBundle:Session')->createQueryBuilder('s')
                    ->andWhere('s.user = :user')->setParameter('user', $user)
                    ->getQuery()->execute();
                foreach ($sessions as $session) { /** @var $session \KreaLab\CommonBundle\Entity\Session */
                    $log = new ActionLog();
                    $log->setUser($user);
                    $log->setActionType('logout');
                    $log->setParams([
                        'ip'     => $session->getIp(),
                        'reason' => 'login_in_other_place',
                    ]);
                    $this->em->persist($log);

                    $this->em->remove($session);
                }

                $log = new ActionLog();
                $log->setUser($user);
                $log->setActionType('login');
                $log->setParams(['ip' => $request->getClientIp()]);
                $this->em->persist($log);
            } else {
                $this->em->getRepository('CommonBundle:Session')->createQueryBuilder('s')
                    ->delete()
                    ->andWhere('s.user = :user')->setParameter('user', $user)
                    ->getQuery()->execute();
            }

            $user->setAuthFailureInfo(null);
            $this->em->persist($user);

            $this->em->flush();
        }

        return new RedirectResponse($this->router->generate('homepage'));
    }
}
