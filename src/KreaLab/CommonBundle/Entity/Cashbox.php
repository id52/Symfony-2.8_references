<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\Cashbox as CashboxModel;

class Cashbox extends CashboxModel
{
    protected $active = true;

    public function getFilial()
    {
        $workplace = $this->getWorkplace();
        return $workplace ? $workplace->getFilial() : null;
    }

    public function __toString()
    {
        return $this->getNum();
    }

    public function getName()
    {
        return $this->getNum().' ('.$this->getInvNum().') «'.$this->getLegalEntity()->getName().'»';
    }
}
