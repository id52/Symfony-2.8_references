<?php

namespace KreaLab\CommonBundle\Listeners;

use Doctrine\ORM\EntityManager;
use KreaLab\CommonBundle\Entity\User;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExtraSession
{
    protected $em;
    protected $token;

    public function __construct(EntityManager $em, TokenStorageInterface $token)
    {
        $this->em    = $em;
        $this->token = $token;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $sessId  = $request->getSession()->getId();
        $session = $this->em->find('CommonBundle:Session', $sessId);
        if ($session) {
            $token = $this->token->getToken();
            $user  = $token ? $token->getUser() : null;
            if ($user instanceof User) {
                $session->setUser($user);
            }

            $session->setIp($request->getClientIp());
            $this->em->persist($session);
            $this->em->flush();
        }
    }
}
