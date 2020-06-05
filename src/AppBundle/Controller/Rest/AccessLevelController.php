<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\AccessLevel;
use AppBundle\Service\AccessLevelService;
use AppBundle\Service\UserService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AccessLevelController extends Controller
{
    use RestControllerTrait;

    /**
     * @Route("/update", methods="PUT")
     *
     * @param Request            $request
     * @param AccessLevelService $accessLevelService
     *
     * @return Response
     */
    public function updateAction(Request $request, AccessLevelService $accessLevelService)
    {
        try {
            $acl = $request->getContent();
            $user = $accessLevelService->update(json_decode($acl, true));

            $payload = [
                "id" => $user->getId(),
                "name" => $user->getName(),
                "username" => $user->getUsername(),
                "email" => $user->getEmail(),
                "acls" => $user->acl(),
            ];

            return $this->renderResponse($payload, Response::HTTP_OK, []);
        } catch (\Exception $e) {
            return $this->exception($e->getMessage(), 400);
        }
    }

    /**
     * exception
     *
     * @param string  $message
     * @param integer $status
     *
     * @return Response
     */
    private function exception($message, $status = 400)
    {
        return $this->renderResponse(array('message' => $message), $status);
    }
}
