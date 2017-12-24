<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\BlankReferencemanReferencemanEnvelope as BlankReferencemanReferencemanEnvelopeModel;
use KreaLab\CommonBundle\Util\IntervalTrait;

class BlankReferencemanReferencemanEnvelope extends BlankReferencemanReferencemanEnvelopeModel
{
    use IntervalTrait;

    public function getLegalEntityShortName()
    {
        return $this->getLegalEntity()->getShortName();
    }
}
