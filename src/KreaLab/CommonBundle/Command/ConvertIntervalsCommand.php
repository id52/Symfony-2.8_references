<?php

namespace KreaLab\CommonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertIntervalsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:convert-intervals');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $input;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $legalEntity = $em->getRepository('CommonBundle:LegalEntity')->findOneBy([]);
        if (!$legalEntity) {
            throw new \RuntimeException('Legal entity is empty');
        }

        $users = $em->getRepository('CommonBundle:User')->findAll();

        $userIntervalsAmount    = 0;
        $userRefIntervalsAmount = 0;

        foreach ($users as $user) { /** @var $user \KreaLab\CommonBundle\Entity\User */
            $invervals = $user->getIntervals();

            if (!empty($invervals) and $this->isOldInterval($invervals)) {
                $newIntervals[$legalEntity->getId()] = $invervals;
                $user->setIntervals($newIntervals);
                $em->persist($user);
                $em->flush();
                $userIntervalsAmount++;
            }

            $referencemanIntervals = $user->getReferencemanIntervals();

            if (!empty($referencemanIntervals) and $this->isOldInterval($referencemanIntervals)) {
                $newIntervals[$legalEntity->getId()] = $referencemanIntervals;
                $user->setReferencemanIntervals($newIntervals);
                $em->persist($user);
                $em->flush();
                $userRefIntervalsAmount++;
            }
        }

        $output->writeln('User stockman intervals = '.$userIntervalsAmount.'/'.count($users));
        $output->writeln('User referenceman intervals = '.$userRefIntervalsAmount.'/'.count($users));

        $operatorEnvelopes = $em->getRepository('CommonBundle:BlankOperatorEnvelope')->findAll();

        $operatorEnvelopesAmount = 0;

        foreach ($operatorEnvelopes as $envelope) {
            /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorEnvelope */

            $invervals = $envelope->getIntervals();

            if (!empty($invervals) and $this->isOldInterval($invervals)) {
                $newIntervals[$legalEntity->getId()] = $invervals;
                $envelope->setIntervals($newIntervals);
                $em->persist($envelope);
                $em->flush();
                $operatorEnvelopesAmount++;
            }
        }

        $output->writeln('Operator envelopes = '.$operatorEnvelopesAmount.'/'.count($operatorEnvelopes));

        $operatorReferencemanEnvelopes = $em->getRepository('CommonBundle:BlankOperatorReferencemanEnvelope')
            ->findAll();

        $operatorRefEnvelopesAmount = 0;

        foreach ($operatorReferencemanEnvelopes as $envelope) {
            /** @var $envelope \KreaLab\CommonBundle\Entity\BlankOperatorReferencemanEnvelope */

            $invervals = $envelope->getIntervals();

            if (!empty($invervals) and $this->isOldInterval($invervals)) {
                $newIntervals[$legalEntity->getId()] = $invervals;
                $envelope->setIntervals($newIntervals);
                $em->persist($envelope);
                $em->flush();

                $operatorRefEnvelopesAmount++;
            }
        }

        $output->writeln('Operator referenceman envelopes = '.$operatorRefEnvelopesAmount
            .'/'.count($operatorReferencemanEnvelopes));

        $refRefEnvelopes = $em->getRepository('CommonBundle:BlankReferencemanReferencemanEnvelope')
            ->findAll();

        $refRefEnvelopesAmount = 0;

        foreach ($refRefEnvelopes as $envelope) {
            /** @var $envelope \KreaLab\CommonBundle\Entity\BlankReferencemanReferencemanEnvelope */

            $invervals = $envelope->getIntervals();

            if (!empty($invervals) and $this->isOldInterval($invervals)) {
                $newIntervals[$legalEntity->getId()] = $invervals;
                $envelope->setIntervals($newIntervals);
                $em->persist($envelope);
                $em->flush();
                $refRefEnvelopesAmount++;
            }
        }

        $output->writeln('Referenceman referenceman envelopes = '.$refRefEnvelopesAmount
            .'/'.count($refRefEnvelopes));

        $stockmanEnvelopesAmount = 0;

        $stockmanEnvelopes = $em->getRepository('CommonBundle:BlankStockmanEnvelope')->findAll();

        foreach ($stockmanEnvelopes as $envelope) {
            /** @var $envelope \KreaLab\CommonBundle\Entity\BlankStockmanEnvelope */

            $invervals = $envelope->getIntervals();

            if (!empty($invervals) and $this->isOldInterval($invervals)) {
                $newIntervals[$legalEntity->getId()] = $invervals;
                $envelope->setIntervals($newIntervals);
                $em->persist($envelope);
                $em->flush();
                $stockmanEnvelopesAmount++;
            }
        }

        $output->writeln('Referenceman stockman envelopes = '.$stockmanEnvelopesAmount.'/'
            .count($stockmanEnvelopes));

        $em->getRepository('CommonBundle:Blank')->createQueryBuilder('b')
            ->update()->set('b.legal_entity', $legalEntity->getId())
            ->andWhere('b.legal_entity IS NULL')
            ->getQuery()->execute();

        $output->writeln('Blank. Legal entity updated');

        $em->getRepository('CommonBundle:BlankOperatorEnvelope')->createQueryBuilder('boe')
            ->update()->set('boe.legal_entity', $legalEntity->getId())
            ->andWhere('boe.legal_entity IS NULL')
            ->getQuery()->execute();

        $output->writeln('Referenceman operator envelope. Legal entity updated');

        $em->getRepository('CommonBundle:BlankOperatorReferencemanEnvelope')->createQueryBuilder('bore')
            ->update()->set('bore.legal_entity', $legalEntity->getId())
            ->andWhere('bore.legal_entity IS NULL')
            ->getQuery()->execute();

        $output->writeln('Operator referenceman envelope. legal entity updated');

        $em->getRepository('CommonBundle:BlankReferencemanReferencemanEnvelope')->createQueryBuilder('brre')
            ->update()->set('brre.legal_entity', $legalEntity->getId())
            ->andWhere('brre.legal_entity IS NULL')
            ->getQuery()->execute();

        $output->writeln('Referenceman referenceman envelope. Legal entity updated');

        $em->getRepository('CommonBundle:BlankStockmanEnvelope')->createQueryBuilder('bse')
            ->update()->set('bse.legal_entity', $legalEntity->getId())
            ->andWhere('bse.legal_entity IS NULL')
            ->getQuery()->execute();

        $output->writeln('Referenceman stockman envelope. Legal entity updated');

        $em->getRepository('CommonBundle:BlankReferencemanEnvelope')->createQueryBuilder('bre')
            ->update()->set('bre.legal_entity', $legalEntity->getId())
            ->andWhere('bre.legal_entity IS NULL')
            ->getQuery()->execute();

        $output->writeln('Stockman referenceman envelope. Legal entity updated');


        $em->getRepository('CommonBundle:BlankLog')->createQueryBuilder('bl')
            ->update()->set('bl.legal_entity', $legalEntity->getId())
            ->andWhere('bl.legal_entity IS NULL')
            ->getQuery()->execute();

        $output->writeln('Blank log. Legal entity updated');

        $output->writeln('Done');
    }

    protected function getArrayDepth(array $array)
    {
        if (!is_array($array)) {
            return 0;
        }

        $maxIndentation = 1;

        // @codingStandardsIgnoreLine
        $lines = explode(PHP_EOL, print_r($array, true));

        foreach ($lines as $line) {
            $indentation    = (strlen($line) - strlen(ltrim($line))) / 4;
            $maxIndentation = max($maxIndentation, $indentation);
        }

        return ceil(($maxIndentation - 1) / 2) + 1;
    }

    protected function isOldInterval(array $array)
    {
        if ($this->getArrayDepth($array) == 4) {
            return true;
        }

        return false;
    }
}
