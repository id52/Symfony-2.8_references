<?php

namespace KreaLab\CommonBundle\Repository;

use Doctrine\ORM\EntityRepository;
use KreaLab\CommonBundle\Entity\LegalEntity;
use KreaLab\CommonBundle\Entity\ReferenceType;
use KreaLab\CommonBundle\Entity\User;
use KreaLab\CommonBundle\Util\BlankIntervals;

class BlankRepository extends EntityRepository
{
    public function getCurrentIntervals(
        User $operator,
        $isStamp = null,
        LegalEntity $legalEntity = null,
        ReferenceType $referenceType = null,
        $serie = null
    ) {
        $intervals = [];

        $uqb = $this->createQueryBuilder('b')
            ->andWhere('b.operator = :operator')->setParameter('operator', $operator)
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByOperator')
        ;

        if ($isStamp !== null) {
            $uqb->andWhere('b.stamp = :stamp')->setParameter('stamp', $isStamp);
        }

        if ($legalEntity !== null) {
            $uqb->andWhere('b.legal_entity = :legal_entity')->setParameter('legal_entity', $legalEntity);
        }

        if ($referenceType !== null) {
            $uqb->andWhere('b.reference_type = :ref_type')->setParameter('ref_type', $referenceType);
        }

        if ($serie !== null) {
            if ($serie == '-') {
                $uqb->andWhere('b.serie = :serie')->setParameter('serie', '');
            } else {
                $uqb->andWhere('b.serie = :serie')->setParameter('serie', $serie);
            }
        }

        $qb   = clone $uqb;
        $data = $qb
            ->select('DISTINCT (b.reference_type) ref_type_id, b.serie, oe.id envelope_id, oe.operator_applied')
            ->leftJoin('b.operator_envelope', 'oe')
            ->orderBy('oe.operator_applied')
            ->getQuery()->getArrayResult();

        $envelopes = [];
        foreach ($data as $value) {
            if (!isset($envelopes[$value['ref_type_id']])) {
                $envelopes[$value['ref_type_id']] = $value;
            }
        }

        ksort($envelopes);

        foreach ($envelopes as $refTypeId => $value) {
            $qb     = clone $uqb;
            $blanks = $qb
                ->andWhere('b.reference_type = :ref_type')->setParameter('ref_type', $refTypeId)
                ->andWhere('b.operator_envelope = :envelope')->setParameter('envelope', $value['envelope_id'])
                ->getQuery()->execute();
            foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
                BlankIntervals::add(
                    $intervals,
                    $blank->getLegalEntity(),
                    $blank->getReferenceType(),
                    $blank->getSerie(),
                    $blank->getNumber(),
                    1,
                    $blank->getLeadingZeros()
                );
            }
        }

        return $intervals;
    }

    public function getCurrentSeries(
        User $operator,
        $isStamp = null,
        LegalEntity $legalEntity = null,
        ReferenceType $referenceType = null,
        $serie = null
    ) {
        $intervals = $this->getCurrentIntervals($operator, $isStamp, $legalEntity, $referenceType, $serie);

        $series = [];
        foreach ($intervals as $legalIntervals) {
            foreach ($legalIntervals as $refIntervals) {
                foreach (array_keys($refIntervals) as $curSerie) {
                    if ($curSerie != '-') {
                        $series[$curSerie] = $curSerie;
                    }
                }
            }
        }

        return $series;
    }

    public function getCurrentNumbers(
        User $operator,
        $isStamp = null,
        LegalEntity $legalEntity = null,
        ReferenceType $referenceType = null,
        $serie = null
    ) {
        $intervals = $this->getCurrentIntervals($operator, $isStamp, $legalEntity, $referenceType, $serie);

        $numbers = [];
        foreach ($intervals as $legalIntervals) {
            foreach ($legalIntervals as $refIntervals) {
                foreach ($refIntervals as $leadingZeros) {
                    foreach ($leadingZeros as $leadingZero => $curIntervals) {
                        foreach ($curIntervals as $curInterval) {
                            for ($i = $curInterval[0]; $i <= $curInterval[1]; $i++) {
                                $number           = str_pad($i, $leadingZero, '0', STR_PAD_LEFT);
                                $numbers[$number] = $number;
                            }
                        }
                    }
                }
            }
        }

        return $numbers;
    }

    public function getCurrentIntervalsFlatten(
        User $operator,
        $isStamp = null,
        LegalEntity $legalEntity = null,
        ReferenceType $referenceType = null,
        $serie = null
    ) {
        $intervals = $this->getCurrentIntervals($operator, $isStamp, $legalEntity, $referenceType, $serie);

        $intervalsFlatten = $intervals;

        foreach ($intervals as $legalEntityId => $legalIntervals) {
            foreach ($legalIntervals as $refTypeId => $refIntervals) {
                foreach ($refIntervals as $serie => $leadingZeros) {
                    foreach ($leadingZeros as $leadingZero => $curIntervals) {
                        $intervalsFlatten[$legalEntityId][$refTypeId][$serie] = [];
                        foreach ($curIntervals as $int) {
                            $int[0] = str_pad($int[0], $leadingZero, '0', STR_PAD_LEFT);
                            $int[1] = str_pad($int[1], $leadingZero, '0', STR_PAD_LEFT);

                            $intervalsFlatten[$legalEntityId][$refTypeId][$serie][] = [
                                'interval' => ($int[0] == $int[1]) ? '['.$int[0].']' : '['.$int[0].', '.$int[1].']',
                                'amount'   => $int[1] - $int[0] + 1,
                            ];
                        }
                    }
                }
            }
        }

        return $intervalsFlatten;
    }

    public function getCurrentIntervalsAmount(
        User $operator,
        $isStamp = null,
        LegalEntity $legalEntity = null,
        ReferenceType $referenceType = null,
        $serie = null
    ) {
        $intervals = $this->getCurrentIntervals($operator, $isStamp, $legalEntity, $referenceType, $serie);

        $amount = 0;

        foreach ($intervals as $legalIntervals) {
            foreach ($legalIntervals as $refIntervals) {
                foreach ($refIntervals as $leadingZeros) {
                    foreach ($leadingZeros as $curIntervals) {
                        foreach ($curIntervals as $int) {
                            $amount += ($int[1] - $int[0] + 1);
                        }
                    }
                }
            }
        }

        return $amount;
    }

    public function getOnHandsAmount(
        User $operator,
        $isStamp = null,
        LegalEntity $legalEntity = null,
        ReferenceType $referenceType = null,
        $serie = null
    ) {
        $qb = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->andWhere('b.operator = :operator')->setParameter('operator', $operator)
            ->andWhere('b.status = :status')->setParameter('status', 'acceptedByOperator')
        ;

        if ($isStamp !== null) {
            $qb->andWhere('b.stamp = :stamp')->setParameter('stamp', $isStamp);
        }

        if ($legalEntity !== null) {
            $qb->andWhere('b.legal_entity = :legal_entity')->setParameter('legal_entity', $legalEntity);
        }

        if ($referenceType !== null) {
            $qb->andWhere('b.reference_type = :ref_type')->setParameter('ref_type', $referenceType);
        }

        if ($serie !== null) {
            if ($serie == '-') {
                $qb->andWhere('b.serie IS NULL');
            } else {
                $qb->andWhere('b.serie = :serie')->setParameter('serie', $serie);
            }
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getLostAndNotPenaltyBlanksByOperator(User $operator)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.status = :status')->setParameter('status', 'lostChecked')
            ->andWhere('b.operator = :operator')->setParameter('operator', $operator)
            ->leftJoin('b.legal_entity', 'le')->addSelect('le')
            ->leftJoin('b.reference_type', 'rt')->addSelect('rt')
            ->andWhere('b.penalty_date IS NULL')
            ->getQuery()->execute();
    }

    public function getLostAndPenaltyBlanksByOperator(User $operator, $chunkByDate = false)
    {
        $blanks = $this->createQueryBuilder('b')
            ->andWhere('b.status = :status')->setParameter('status', 'lostChecked')
            ->andWhere('b.operator = :operator')->setParameter('operator', $operator)
            ->leftJoin('b.legal_entity', 'le')->addSelect('le')
            ->leftJoin('b.reference_type', 'rt')->addSelect('rt')
            ->andWhere('b.penalty_date IS NOT NULL')
            ->orderBy('b.penalty_date', 'DESC')
            ->getQuery()->execute();

        if (!$chunkByDate) {
            return $blanks;
        }

        $blanksByDate = [];
        foreach ($blanks as $blank) { /** @var $blank \KreaLab\CommonBundle\Entity\Blank */
            $date = $blank->getPenaltyDate()->format('Y-m-d');
            if (!isset($blanksByDate[$date])) {
                $blanksByDate[$date] = [
                    'date'   => $blank->getPenaltyDate(),
                    'blanks' => [],
                    'cnt'    => 0,
                    'sum'    => 0,
                ];
            }

            $blanksByDate[$date]['blanks'][] = $blank;
            $blanksByDate[$date]['sum']      = $blanksByDate[$date]['sum'] + $blank->getPenaltySum();
            $blanksByDate[$date]['cnt']      = $blanksByDate[$date]['cnt'] + 1;
        }

        return $blanksByDate;
    }
}
