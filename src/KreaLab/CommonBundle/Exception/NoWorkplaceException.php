<?php

namespace KreaLab\CommonBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class NoWorkplaceException extends AccountStatusException
{
    public function getMessageKey()
    {
        return 'No workplace.';
    }
}
