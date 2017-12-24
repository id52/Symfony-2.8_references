<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\ReferenceType as ReferenceTypeModel;

class ReferenceType extends ReferenceTypeModel
{
    public function __toString()
    {
        return $this->getName();
    }
}
