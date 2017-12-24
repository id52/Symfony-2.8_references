<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\Blank as BlankModel;

class Blank extends BlankModel
{
    public function getLegalEntityShortName()
    {
        return $this->getLegalEntity()->getShortName();
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return str_pad(parent::getNumber(), $this->getLeadingZeros(), '0', STR_PAD_LEFT);
    }
}
