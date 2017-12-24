<?php

namespace KreaLab\CommonBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class TempLockedException extends AccountStatusException
{
    public function getMessageKey()
    {
        return 'Account is temporarily locked.';
    }
}
