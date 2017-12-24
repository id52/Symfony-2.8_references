<?php

namespace KreaLab\CommonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReferencemanIntervalsSyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:referenceman-intervals-sync');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $users = $em->getRepository('CommonBundle:User')->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role_referenceman')->setParameter('role_referenceman', '%ROLE_REFERENCEMAN%')
            ->getQuery()->getResult();

        foreach ($users as $user) { /** @var $user \KreaLab\CommonBundle\Entity\User */
            $user->setReferencemanIntervals([]);
            $em->persist($user);
        }

        $em->flush();

        $blanks = $em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByReferenceman')
            ->andWhere('b.referenceman IS NOT NULL')
            ->getQuery()->getResult();

        foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $blank->getReferenceman()->addReferencemanInterval(
                $blank->getLegalEntity(),
                $blank->getReferenceType(),
                $blank->getSerie(),
                $blank->getNumber(),
                1,
                $blank->getLeadingZeros()
            );

            $em->persist($blank);
        }

        $em->flush();

        $output->writeln('Done');
    }
}
