<?php

/**
 * Added in crontab
 * 0 5 * * 1
 */

namespace KreaLab\CommonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearImagesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:clear-images')
            ->addOption('cron', 'c', InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em     = $this->getContainer()->get('doctrine.orm.entity_manager');
        $cnt    = 0;
        $images = $em->getRepository('CommonBundle:Image')->createQueryBuilder('i')
            ->andWhere('i.service_log IS NULL')
            ->getQuery()->execute();
        foreach ($images as $image) {
            $em->remove($image);
            $em->flush();

            $cnt++;
        }

        if ($cnt) {
            $cron = $input->getOption('cron') ? date('Y-m-d H:i:s').' | ' : '';
            $output->writeln($cron.'Removed <info>'.$cnt.'</info> images.');
        }
    }
}
