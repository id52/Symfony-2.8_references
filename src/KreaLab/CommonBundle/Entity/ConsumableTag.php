<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\ConsumableTag as ConsumableTagModel;

class ConsumableTag extends ConsumableTagModel
{
    public function __toString()
    {
        return $this->getName();
    }
}
