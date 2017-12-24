<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\Workplace as WorkplaceModel;

class Workplace extends WorkplaceModel
{
    protected $active = true;
    protected $sum = 0;

    public function __toString()
    {
        return $this->getName();
    }

    public function getNameWithLegalEntity()
    {
        return $this->getName().' ('.$this->getLegalEntity().') ';
    }

    public function isActiveAll()
    {
        return $this->getActive() && $this->getFilial()->getActive();
    }

    public function getLegalEntityShortName()
    {
        $legal_entity = $this->getLegalEntity();
        if ($legal_entity) {
            return $legal_entity->getShortName();
        }
        return null;
    }
}
