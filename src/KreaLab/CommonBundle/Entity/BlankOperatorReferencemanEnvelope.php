<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\BlankOperatorReferencemanEnvelope as BlankOperatorReferencemanEnvelopeModel;
use KreaLab\CommonBundle\Util\IntervalTrait;

class BlankOperatorReferencemanEnvelope extends BlankOperatorReferencemanEnvelopeModel
{
    use IntervalTrait;

    public function getLegalEntityShortName()
    {
        return $this->getLegalEntity()->getShortName();
    }

}
