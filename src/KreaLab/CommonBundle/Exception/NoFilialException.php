<?php

namespace KreaLab\CommonBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class NoFilialException extends AccountStatusException
{
    public function getMessageKey()
    {
        return 'No filial.';
    }
}
