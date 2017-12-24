<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\Agreement as AgreementModel;

class Agreement extends AgreementModel
{
    public function getExecutorOrGuarantor()
    {
        if ($this->getType() == 'bilateral') {
            return $this->getExecutor();
        } else if ($this->getType() == 'tripartite') {
            return $this->getGuarantor();
        }

        return null;
    }
}
