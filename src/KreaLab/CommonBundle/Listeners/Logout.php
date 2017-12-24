<?php

namespace KreaLab\CommonBundle\Listeners;

use Doctrine\ORM\EntityManager;
use KreaLab\CommonBundle\Entity\ActionLog;
use KreaLab\CommonBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class Logout implements LogoutSuccessHandlerInterface
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

    public function onLogoutSuccess(Request $request)
    {
        $token = $this->token->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user instanceof User && $user->isOperator()) {
                $filials = $user->getFilials();
                $filial  = $filials[0];

                $shiftLog = $this->em->getRepository('CommonBundle:ShiftLog')->createQueryBuilder('s')
                    ->andWhere('s.user = :user')->setParameter('user', $user)
                    ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
                    ->andWhere('s.date = :date')->setParameter('date', new \DateTime('today'))
                    ->andWhere('s.endTime IS NULL')
                    ->setMaxResults(1)
                    ->getQuery()->getOneOrNullResult();

                if ($shiftLog) {
                    $shiftLog->setEndTime(new \DateTime());
                    $this->em->persist($shiftLog);
                    $this->em->flush();
                }

                $log = new ActionLog();
                $log->setUser($user);
                $log->setActionType('logout');
                $log->setParams(['ip' => $request->getClientIp()]);
                $this->em->persist($log);
                $this->em->flush();
            }
        }

        return new RedirectResponse($this->router->generate('homepage'));
    }
}
