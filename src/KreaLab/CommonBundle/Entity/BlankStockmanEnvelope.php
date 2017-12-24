<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\BlankStockmanEnvelope as BlankStockmanEnvelopeModel;
use KreaLab\CommonBundle\Util\IntervalTrait;

class BlankStockmanEnvelope extends BlankStockmanEnvelopeModel
{
    use IntervalTrait;

    public function getLegalEntityShortName()
    {
        return $this->getLegalEntity()->getShortName();
    }
}
