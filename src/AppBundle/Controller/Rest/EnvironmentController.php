<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\Environment;
use AppBundle\Service\EnvironmentService;
use AppBundle\Exception\DuplicateApplicationNameException;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EnvironmentController extends Controller
{
    use RestControllerTrait;

    /**
     * @Route("/list", methods="GET")
     * @param Request $request
     * @param EnvironmentService $environmentService
     * @return Response
     */
    public function getAllEnvironments(Request $request, EnvironmentService $environmentService)
    {
        return $this->renderResponse($environmentService->list(), Response::HTTP_OK, ['minimalist']);
    }


    /**
     * @Route("/update", methods="PUT")
     *
     * @param Request $request
     * @param EnvironmentService $environmentService
     * @return Response
     */
    public function updateEnvironment(Request $request, EnvironmentService $environmentService)
    {
        // Only super Admin can do this
       // $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);

        $data = $request->getContent();
        $successful = $environmentService->update($data);
        return $this->renderResponse($successful);
    }

    /**
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param EnvironmentService $environmentService
     *
     * @return Response
     */
    public function addEnvironment(Request $request, EnvironmentService $environmentService)
    {
        // Only super Admin can do this
        //$this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
        try {
            $data = $request->getContent();
            $successful = $environmentService->add($data);
            return $this->renderResponse($successful);
        }
        catch (DuplicateApplicationNameException $e) {
            return $this->renderResponse(['message' => $e->getMessage()], Response::HTTP_NOT_ACCEPTABLE);
        }
        catch (\Throwable $e) {
            return $this->renderResponse(['message' => $e->getMessage()], Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * @Route("/{id}/delete", methods="DELETE")
     *
     * @param Request $request
     * @param EnvironmentService $environmentService
     * @param string $id
     * @return Response
     */
    public function deleteEnvironment(Request $request, EnvironmentService $environmentService, $id)
    {
        // Only super Admin can do this
        //$this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);

        $environmentService->delete($id);

        return $this->renderResponse(null, Response::HTTP_OK);
    }

    /**
     * @Route("/{id}/get", methods="GET")
     *
     * @param Request $request
     * @param EnvironmentService $environmentService
     * @param string $id
     * @return Response
     */
    public function getById(Request $request, EnvironmentService $environmentService, $id)
    {
        $data = $environmentService->getById($id);

        return $this->renderResponse($data, Response::HTTP_OK, ["minimalist"]);
    }

    /**
     * @Route("/{id}/get/minimalist", methods="GET")
     *
     * @param Request $request
     * @param EnvironmentService $environmentService
     * @param string $id
     * @return Response
     */
    public function getMinimalistById(Request $request, EnvironmentService $environmentService, $id)
    {
        $data = $environmentService->getById($id);

        return $this->renderResponse($data, Response::HTTP_OK, ["minimalist"]);
    }

    /**
     * @Route("/list/minimalist", methods="GET")
     * @param Request $request
     * @param EnvironmentService $environmentService
     * @return Response
     */
    public function getAllMinimalistEnvironments(Request $request, EnvironmentService $environmentService)
    {
        return $this->renderResponse($environmentService->list(), Response::HTTP_OK, ['minimalist']);
    }

    /**
     * @Route("/default", methods="GET")
     *
     * @param Request $request
     * @param EnvironmentService $environmentService
     * @param string $id
     * @return Response
     */
    public function getDefault(Request $request, EnvironmentService $environmentService)
    {
        $data = $environmentService->getDefault();

        return $this->renderResponse($data, Response::HTTP_OK, ["minimalist"]);
    }

    /**
     * @Route("/{id}/default", methods="PUT")
     *
     * @param Request $request
     * @param EnvironmentService $environmentService
     * @param string $id
     * @return Response
     */
    public function setDefault(Request $request, EnvironmentService $environmentService, $id)
    {
        $data = $environmentService->setDefault($id);

        return $this->renderResponse($data, Response::HTTP_OK, ["minimalist"]);
    }

}
