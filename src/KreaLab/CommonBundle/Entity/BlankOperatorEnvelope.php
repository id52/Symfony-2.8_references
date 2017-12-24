<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\BlankOperatorEnvelope as BlankOperatorEnvelopeModel;
use KreaLab\CommonBundle\Util\IntervalTrait;

class BlankOperatorEnvelope extends BlankOperatorEnvelopeModel
{
    use IntervalTrait;

    public function __toString()
    {
        return (string)$this->getId();
    }

    public function getLegalEntityShortName()
    {
        return $this->getLegalEntity()->getShortName();
    }
}
