<?php

namespace KreaLab\CommonBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class InvalidUserScheduleException extends AccountStatusException
{
    public function getMessageKey()
    {
        return 'No schedule for user.';
    }
}
