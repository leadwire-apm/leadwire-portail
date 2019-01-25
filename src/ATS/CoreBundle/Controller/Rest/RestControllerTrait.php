<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Controller\Rest;

use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * BaseRestController
 *
 * @author Ali TURKI <aturki@ats-digital.com>
 *
 * @property ContainerInterface $container
 */
trait RestControllerTrait
{

    /**
     * Renders Response
     *
     * @param mixed $data
     * @param int $responseCode
     * @param array $contextGroups
     *
     * @return JsonResponse
     */
    protected function renderResponse($data, $responseCode = Response::HTTP_OK, $contextGroups = [])
    {
        $response = null;

        $serializer = $this->container->get('jms_serializer');

        $context = SerializationContext::create()->enableMaxDepthChecks();

        if (count($contextGroups) > 0) {
            $context->setGroups($contextGroups);
        }

        try {
            $jsonData = $serializer->serialize(
                $data,
                'json',
                $context
            );

            $response = new JsonResponse($jsonData, $responseCode, [], true);
        } catch (\Exception $e) {
            $error = [
                'message' => $e->getMessage(),
            ];

            $response = new JsonResponse(
                json_encode($error),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $response;
    }
}
