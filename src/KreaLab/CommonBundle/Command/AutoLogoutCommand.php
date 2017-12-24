<?php

/**
 * Added in crontab
 * * * * * *
 */

namespace KreaLab\CommonBundle\Command;

use KreaLab\CommonBundle\Entity\ActionLog;
use KreaLab\CommonBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AutoLogoutCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:auto-logout')
            ->addOption('cron', 'c', InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em       = $this->getContainer()->get('doctrine.orm.entity_manager');
        $cnt      = 0;
        $cntOp    = 0;
        $sessions = $em->getRepository('CommonBundle:Session')->createQueryBuilder('s')
            ->andWhere('(s.sess_time + 1800) < UNIX_TIMESTAMP()')
            ->getQuery()->execute();

        foreach ($sessions as $session) { /** @var $session \KreaLab\CommonBundle\Entity\Session */
            $user = $session->getUser();
            if ($user instanceof User && $user->isOperator()) {
                $log = new ActionLog();
                $log->setUser($user);
                $log->setActionType('logout');
                $log->setParams([
                    'ip'     => $session->getIp(),
                    'reason' => 'autologout',
                ]);
                $em->persist($log);

                $cntOp ++;
            }

            $em->remove($session);
            $em->flush();

            $cnt ++;
        }

        $cron = $input->getOption('cron') ? date('Y-m-d H:i:s').' | ' : '';
        if ($cnt) {
            $output->writeln($cron.'Removed <info>'.$cnt.'</info> sessions.');
        }

        if ($cntOp) {
            $output->writeln($cron.'Logouted <info>'.$cntOp.'</info> operators.');
        }
    }
}
