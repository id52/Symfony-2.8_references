<?php

namespace KreaLab\CommonBundle\Listeners;

use Doctrine\ORM\EntityManager;
use KreaLab\CommonBundle\Entity\User;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ForceChangePass
{
    protected $em;
    protected $token;
    protected $twig;

    public function __construct(
        EntityManager $em,
        TokenStorageInterface $token,
        \Twig_Environment $twig
    ) {
        $this->em    = $em;
        $this->token = $token;
        $this->twig  = $twig;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $token = $this->token->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user instanceof User) {
                if ($user->getForceChangePass()) {
                    $request->attributes->set('_controller', 'AppBundle:Default:forceChangePass');
                }
            }
        }
    }
}
