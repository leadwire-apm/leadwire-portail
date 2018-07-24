<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Service\AuthService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use Doctrine\Common\EventSubscriber;
use Firebase\JWT\ExpiredException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class JWTSubscriber implements EventSubscriberInterface
{

    public function __construct(AuthService $auth) {
        $this->auth = $auth;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();
        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof BaseRestController && $controller[0]->isNotPublic()) {
            if (!$request->headers->has('Authorization')) {
                throw new HttpException( 401);
            }

            $headerParts = explode(' ', $request->headers->get('Authorization'));
            if (!(count($headerParts) === 2 && $headerParts[0] === 'Bearer')) {
                throw new HttpException(401);
            }
            try{
                $token = $this->auth->decodeToken($headerParts[1]);
            } catch (ExpiredException $e) {
                throw new HttpException(401);
            }
        }
    }


    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }

}