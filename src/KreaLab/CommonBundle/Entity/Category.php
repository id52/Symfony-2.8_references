<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\Category as CategoryModel;

class Category extends CategoryModel
{
    public function __toString()
    {
        return $this->getName();
    }
}
