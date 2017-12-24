<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\Man as ManModel;

class Man extends ManModel
{
    public function __toString()
    {
        return $this->getLastName().' '.$this->getFirstName().' '.$this->getPatronymic();
    }

    public function getFullName()
    {
        return $this->getLastName().' '.$this->getFirstName().' '.$this->getPatronymic();
    }

    public function getFullNameGenitive()
    {
        return $this->getLastNameGenitive().' '.$this->getFirstNameGenitive().' '.$this->getPatronymicGenitive();
    }
}
