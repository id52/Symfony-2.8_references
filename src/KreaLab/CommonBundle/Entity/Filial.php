<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\Filial as FilialModel;

class Filial extends FilialModel
{
    protected $active = true;

    public function __toString()
    {
        return $this->getName();
    }

    public function getActiveWorkplaces()
    {
        $workplaces = [];
        foreach ($this->workplaces as $workplace) { /** @var \KreaLab\CommonBundle\Entity\Workplace */
            if ($workplace->getActive()) {
                $workplaces[] = $workplace;
            }
        }
        return $workplaces;
    }
}
