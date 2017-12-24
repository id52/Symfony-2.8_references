<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\Specialty as SpecialtyModel;

class Specialty extends SpecialtyModel
{
    public function __toString()
    {
        return $this->getName();
    }

    public function getEegString()
    {
        if ($this->getEeg()) {
            return 'Да';
        } else {
            return 'Нет';
        }
    }
}
