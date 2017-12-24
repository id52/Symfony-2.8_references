<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\BlankReferencemanEnvelope as BlankReferencemanEnvelopeModel;
use KreaLab\CommonBundle\Util\IntervalTrait;

class BlankReferencemanEnvelope extends BlankReferencemanEnvelopeModel
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
