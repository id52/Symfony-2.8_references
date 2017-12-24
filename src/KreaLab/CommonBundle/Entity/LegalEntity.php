<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\LegalEntity as LegalEntityModel;

class LegalEntity extends LegalEntityModel
{
    protected $active = true;

    public function __toString()
    {
        return $this->getName();
    }

    public function getNameAndShortName()
    {
        return $this->getName().' - '.$this->getShortName();
    }
}
