<?php

namespace KreaLab\CommonBundle\Listeners;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthFailure extends DefaultAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    protected $em;

    public function __construct(
        EntityManager $em,
        HttpKernelInterface $httpKernel,
        HttpUtils $httpUtils,
        array $options = [],
        LoggerInterface $logger = null
    ) {
        $this->em = $em;
        parent::__construct($httpKernel, $httpUtils, $options, $logger);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof BadCredentialsException) {
            $prevException = $exception->getPrevious();
            if ($prevException instanceof BadCredentialsException) {
                $username = $exception->getToken()->getUsername();
                $user     = $this->em->getRepository('CommonBundle:User')->findOneBy(['username' => $username]);
                if ($user) {
                    $info   = $user->getAuthFailureInfo();
                    $info[] = new \DateTime();
                    $user->setAuthFailureInfo($info);
                    $this->em->persist($user);
                    $this->em->flush();
                }
            }
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}
