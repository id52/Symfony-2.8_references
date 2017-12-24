<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\Brigade as BrigadeModel;

class Brigade extends BrigadeModel
{
    public function __toString()
    {
        return $this->getName();
    }
}
