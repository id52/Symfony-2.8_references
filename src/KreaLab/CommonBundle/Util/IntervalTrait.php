<?php

namespace KreaLab\CommonBundle\Util;

trait IntervalTrait
{
    /**
     * @param $legalEntity   \KreaLab\CommonBundle\Entity\LegalEntity
     * @param $referenceType \KreaLab\CommonBundle\Entity\ReferenceType
     * @param $serie         string
     * @param $start         int
     * @param $count         int
     */
    public function addInterval(
        $legalEntity,
        $referenceType,
        $serie,
        $start,
        $count = 1,
        $leadingZeros = 0
    ) {
        if (!$this->intervals) {
            $this->intervals = [];
        }

        BlankIntervals::add($this->intervals, $legalEntity, $referenceType, $serie, $start, $count, $leadingZeros);
    }

    /**
     * @param $legalEntity   \KreaLab\CommonBundle\Entity\LegalEntity
     * @param $referenceType \KreaLab\CommonBundle\Entity\ReferenceType
     * @param $serie         string
     * @param $start         int
     * @param $count         int
     */
    public function removeInterval(
        $legalEntity,
        $referenceType,
        $serie,
        $start,
        $count = 1,
        $leadingZeros = 0
    ) {
        if (!$this->intervals) {
            $this->intervals = [];
        }

        BlankIntervals::remove($this->intervals, $legalEntity, $referenceType, $serie, $start, $count, $leadingZeros);
    }

    public function countIntervals()
    {
        if (!$this->intervals) {
            $this->intervals = [];
        }

        return BlankIntervals::count($this->intervals);
    }
}
