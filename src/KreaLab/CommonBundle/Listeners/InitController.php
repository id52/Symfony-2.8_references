<?php

namespace KreaLab\CommonBundle\Listeners;

use KreaLab\CommonBundle\Exception\CommonResponseException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class InitController
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }

        $controller = $controller[0];
        if (method_exists($controller, 'init')) {
            $return = call_user_func([$controller, 'init']);
            if ($return) {
                throw new CommonResponseException($return);
            }
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof CommonResponseException) {
            $event->setResponse($exception->getResponse());
        }
    }
}
