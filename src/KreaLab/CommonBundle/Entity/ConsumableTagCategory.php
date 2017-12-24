<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\ConsumableTagCategory as ConsumableTagCategoryModel;

class ConsumableTagCategory extends ConsumableTagCategoryModel
{
    public function __toString()
    {
        return $this->getName();
    }
}
