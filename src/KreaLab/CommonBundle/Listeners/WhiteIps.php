<?php

namespace KreaLab\CommonBundle\Listeners;

use Doctrine\ORM\EntityManager;
use KreaLab\CommonBundle\Entity\ActionLog;
use KreaLab\CommonBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WhiteIps
{
    protected $em;
    protected $router;
    protected $token;

    public function __construct(EntityManager $em, RouterInterface $router, TokenStorageInterface $token)
    {
        $this->em     = $em;
        $this->router = $router;
        $this->token  = $token;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $token   = $this->token->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user instanceof User) {
                $ipAddresses = $user->getIps();
                if ($ipAddresses) {
                    $ipAddress = $request->getClientIp();
                    if (!in_array($ipAddress, $ipAddresses)) {
                        if ($user->isOperator()) {
                            $log = new ActionLog();
                            $log->setUser($user);
                            $log->setActionType('logout');
                            $log->setParams([
                                'ip'     => $request->getClientIp(),
                                'reason' => 'bad_ip',
                            ]);
                            $this->em->persist($log);
                            $this->em->flush();
                        }

                        $this->token->setToken(null);
                        $request->getSession()->invalidate();
                        $response = new RedirectResponse($this->router->generate('login'));
                        $event->setResponse($response);

                        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
                        $session = $request->getSession();
                        $session->getFlashBag()->add('danger', 'Вы находитесь не на своем рабочем месте.');
                    }
                }
            }
        }
    }
}
