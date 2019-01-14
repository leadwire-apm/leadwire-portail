<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Controller\Rest;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * BaseRestController
 *
 * @author Ali Turki <aturki@ats-digital.com>
 *
 */
class BaseRestController extends FOSRestController
{

    /**
     * Prepares a JSON Response
     *
     * @param mixed $data
     * @param int $responseCode
     * @param string $contextGroup
     *
     * @return Response
     */
    protected function prepareJsonResponse($data, $responseCode = Response::HTTP_OK, $contextGroup = null)
    {

        $view = $this->view($data, $responseCode);

        if ($contextGroup !== null) {
            $context = new Context();
            $context->addGroup($contextGroup);
            $view->setContext($context);
        }

        $view->setTemplate('CoreBundle:Rest:data.html.twig');

        return $this->handleView($view);
    }
}
