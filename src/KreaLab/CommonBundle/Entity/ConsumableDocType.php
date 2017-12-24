<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\ConsumableDocType as ConsumableDocTypeModel;

class ConsumableDocType extends ConsumableDocTypeModel
{
    /**
     * @var boolean
     */
    protected $active = true;

    public function __toString()
    {
        return $this->getName();
    }
}
