<?php

namespace KreaLab\CommonBundle\Security;

use Doctrine\ORM\EntityManager;
use KreaLab\CommonBundle\Entity\ActionLog;
use KreaLab\CommonBundle\Entity\ShiftLog;
use KreaLab\CommonBundle\Entity\User;
use KreaLab\CommonBundle\Exception\InvalidFilialScheduleException;
use KreaLab\CommonBundle\Exception\InvalidUserScheduleException;
use KreaLab\CommonBundle\Exception\NoFilialException;
use KreaLab\CommonBundle\Exception\NoWorkplaceException;
use KreaLab\CommonBundle\Exception\TempLockedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserChecker as BaseUserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker extends BaseUserChecker
{
    /** @var $em EntityManager */
    protected $em;
    /** @var  $request Request */
    protected $request;

    public function __construct(EntityManager $em, RequestStack $request)
    {
        $this->em      = $em;
        $this->request = $request->getCurrentRequest();
    }

    public function checkPreAuth(UserInterface $user)
    {
        if (!($user instanceof User)) {
            return;
        }

        if ($user instanceof User && $user->hasOnlyRole('ROLE_OPERATOR')) {
            $workplace = $user->getWorkplace();
            if (!$workplace) {
                throw new NoWorkplaceException();
            }

            $filial = $user->getWorkplace()->getFilial();

            if (!$filial) {
                throw new NoFilialException();
            }

            $filialSchedule = $this->em->getRepository('CommonBundle:Schedule')
                ->createQueryBuilder('s')
                ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
                ->andWhere('s.date = :date')->setParameter('date', new \DateTime('today'))
                ->andWhere('s.startTime <= :now AND :now <= s.endTime')->setParameter('now', new \DateTime())
                ->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();

            if (!$filialSchedule) {
                $log = new ActionLog();
                $log->setUser($user);
                $log->setActionType('login_attempt');
                $log->setParams([
                    'ip'     => $this->request->getClientIp(),
                    'reason' => 'no_filial_schedule',
                ]);
                $this->em->persist($log);
                $this->em->flush();

                throw new InvalidFilialScheduleException();
            }

            $operatorSchedule = $this->em->getRepository('CommonBundle:OperatorSchedule')
                ->createQueryBuilder('os')
                ->andWhere('os.operator = :operator')->setParameter('operator', $user)
                ->andWhere('os.date = :date')->setParameter('date', new \DateTime('today'))
                ->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();

            if (!$operatorSchedule) {
                $log = new ActionLog();
                $log->setUser($user);
                $log->setActionType('login_attempt');
                $log->setParams([
                    'ip'     => $this->request->getClientIp(),
                    'reason' => 'no_operator_schedule',
                ]);
                $this->em->persist($log);
                $this->em->flush();

                throw new InvalidUserScheduleException();
            }

            $shiftLog = new ShiftLog();
            $shiftLog->setUser($user);
            $shiftLog->setFilial($filial);
            $shiftLog->setDate(new \DateTime());
            $shiftLog->setStartTime(new \DateTime());
            $this->em->persist($shiftLog);
            $this->em->flush();
        }

        $info = $user->getAuthFailureInfo();
        if (count($info) == 0) {
            return;
        }

        /** @var $last \DateTime */
        $last = clone end($info);
        $act  = [];
        if ($last > new \DateTime('-5 minutes')) {
            $stop = $last->sub(new \DateInterval('PT5M'));
            foreach ($info as $time) {
                if ($time > $stop) {
                    $act[] = $time;
                }
            }

            if (count($act) >= 3) {
                $exeption = new TempLockedException();
                $exeption->setUser($user);
                throw $exeption;
            }
        }

        $user->setAuthFailureInfo($act);
        $this->em->persist($user);
        $this->em->flush();
    }
}
