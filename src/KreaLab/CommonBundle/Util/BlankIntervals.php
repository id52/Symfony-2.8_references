<?php

namespace KreaLab\CommonBundle\Util;

use KreaLab\CommonBundle\Entity\LegalEntity;
use KreaLab\CommonBundle\Entity\ReferenceType;

class BlankIntervals
{
    /**
     * @param $intervals     &array
     * @param $legalEntity   \KreaLab\CommonBundle\Entity\LegalEntity
     * @param $referenceType \KreaLab\CommonBundle\Entity\ReferenceType
     * @param $serie         string
     * @param $start         int
     * @param $count         int
     */
    public static function add(
        array &$intervals,
        LegalEntity $legalEntity,
        ReferenceType $referenceType,
        $serie,
        $start,
        $count = 1,
        $leadingZeros = 0
    ) {
        $start  = (int)$start;
        $finish = $start + $count - 1;
        $serie  = trim($serie) ?: '-';

        $referenceTypeId = $referenceType->getId();
        $legalEntityId   = $legalEntity->getId();

        if (!$referenceTypeId || !$legalEntityId) {
            return;
        }

        if (empty($intervals[$legalEntityId][$referenceTypeId][$serie][$leadingZeros])) {
            $intervals[$legalEntityId][$referenceTypeId][$serie][$leadingZeros][] = [$start, $finish];

            return;
        }

        $curInterval = &$intervals[$legalEntityId][$referenceTypeId][$serie][$leadingZeros];

        $isFirst = true;
        foreach ($curInterval as $k => &$v) {
            if ($start > $v[1]) {
                if (isset($curInterval[$k + 1])) {
                    if ($finish < $curInterval[$k + 1][0]) {
                        if ($finish + 1 == $curInterval[$k + 1][0]) {
                            if ($v[1] + 1 == $start) {
                                $curInterval[$k][1] = $curInterval[$k + 1][1];
                                unset($curInterval[$k + 1]);
                                break;
                            } else {
                                $curInterval[$k + 1][0] = $start;
                                break;
                            }
                        } else {
                            if ($v[1] + 1 == $start) {
                                $curInterval[$k][1] = $finish;
                                break;
                            } else {
                                $curInterval[] = [$start, $finish];
                                break;
                            }
                        }
                    }
                } else {
                    if ($v[1] + 1 == $start) {
                        $curInterval[$k][1] = $finish;
                        break;
                    } else {
                        $curInterval[] = [$start, $finish];
                        break;
                    }
                }
            } elseif ($isFirst && $finish < $v[0]) {
                if ($finish + 1 == $v[0]) {
                    $curInterval[$k][0] = $start;
                    break;
                } else {
                    $curInterval[] = [$start, $finish];
                    break;
                }
            }

            $isFirst = false;
        }

        usort($curInterval, function ($first, $second) {
            return ($first[0] < $second[0]) ? -1 : 1;
        });
    }

    /**
     * @param $intervals     &array
     * @param $legalEntity   \KreaLab\CommonBundle\Entity\LegalEntity
     * @param $referenceType \KreaLab\CommonBundle\Entity\ReferenceType
     * @param $serie         string
     * @param $start         int
     * @param $count         int
     */
    public static function remove(
        array &$intervals,
        LegalEntity $legalEntity,
        ReferenceType $referenceType,
        $serie,
        $start,
        $count = 1,
        $leadingZeros = 0
    ) {
        $finish = $start + $count - 1;

        $serie = trim($serie) ?: '-';

        $referenceTypeId = $referenceType->getId();
        $legalEntityId   = $legalEntity->getId();

        if (!$referenceTypeId || !$legalEntityId) {
            return;
        }

        if (!isset($intervals[$legalEntityId][$referenceTypeId][$serie][$leadingZeros])) {
            return;
        }

        $curInterval = &$intervals[$legalEntityId][$referenceTypeId][$serie][$leadingZeros];

        foreach ($curInterval as $k => &$v) {
            if ($start >= $v[0] && $finish <= $v[1]) {
                if ($start == $v[0]) {
                    if ($finish == $v[1]) {
                        unset($curInterval[$k]);
                        break;
                    } elseif ($finish < $v[1]) {
                        $curInterval[$k][0] = $finish + 1;
                        break;
                    }
                } elseif ($start > $v[0]) {
                    if ($finish == $v[1]) {
                        $curInterval[$k][1] = $start - 1;
                        break;
                    } elseif ($finish < $v[1]) {
                        $curInterval[]      = [$finish + 1, $curInterval[$k][1]];
                        $curInterval[$k][1] = $start - 1;
                        break;
                    }
                }
            }
        }

        usort($curInterval, function ($first, $second) {
            return ($first[0] < $second[0]) ? -1 : 1;
        });

        self::clear($intervals);
    }

    /**
     * @param $intervals array
     */
    public static function count(array $intervals)
    {
        $cnt = 0;

        foreach ($intervals as &$legalEntity) {
            foreach ($legalEntity as $referenceType) {
                foreach ($referenceType as $serie) {
                    foreach ($serie as $leadingZeros) {
                        foreach ($leadingZeros as $interval) {
                            $cnt += $interval[1] - $interval[0] + 1;
                        }
                    }
                }
            }
        }

        return $cnt;
    }

    /**
     * @param $intervals &array
     */
    public static function clear(array &$intervals)
    {
        self::filterArray($intervals);
    }

    /**
     * @param $array &array
     */
    private static function filterArray(array &$array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                self::filterArray($value);
                if (count($value) == 0) {
                    unset($array[$key]);
                }
            }
        }
    }

    /**
     * @param $intervals     array
     * @param $legalEntity   \KreaLab\CommonBundle\Entity\LegalEntity
     * @param $referenceType \KreaLab\CommonBundle\Entity\ReferenceType
     * @param $serie         string
     * @return int
     */
    public static function isExist(
        array $intervals,
        LegalEntity $legalEntity,
        ReferenceType $referenceType,
        $serie,
        $leadingZeros,
        $numBlank
    ) {
        $serie  = $serie == '' ? '-' : $serie;
        $number = null;

        if (isset($intervals[$legalEntity->getId()][$referenceType->getId()][$serie][$leadingZeros])) {
            $curIntervals = $intervals[$legalEntity->getId()][$referenceType->getId()][$serie][$leadingZeros];
            foreach ($curIntervals as &$interval) {
                for ($i = $interval[0]; $i <= $interval[1]; $i ++) {
                    if ($numBlank == $i) {
                        $number = $i;
                    }
                }
            }
        }

        return $number;
    }
}
