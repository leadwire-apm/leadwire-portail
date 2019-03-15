<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\User;
use AppBundle\Service\TmecService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TmecController extends Controller
{

    use RestControllerTrait;

    /**
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param TmecService $tmecService
     *
     * @return Response
     */
    public function newTmecAction(Request $request, TmecService $tmecService)
    {
         // Only super Admin can do this
         $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
        $data = json_decode($request->getContent(), true);
        $tmec = $tmecService->newTmec($data);
        return $this->renderResponse($tmec);
    }

    /**
     * @Route("/update", methods="POST")
     *
     * @param Request $request
     * @param TmecService $tmecService
     *
     * @return Response
     */
    public function updateTmecAction(Request $request, TmecService $tmecService)
    {
        // Only super Admin can do this
        $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
        $data = $request->getContent();
        $successful = $tmecService->update($data);
        return $this->renderResponse($successful);
    }

    /**
     * @Route("/list", methods="POST")
     *
     * @param Request $request
     * @param TmecService $tmecService
     * 
     * @return Response
     */
    public function listTmecAction(Request $request, TmecService $tmecService)
    {
        $data = json_decode($request->getContent(), true);
        $tmec = $tmecService->listTmec($data);
        return $this->renderResponse($tmec);
    }

    /**
     * @Route("/find/{id}", methods="GET")
     *
     * @param Request $request
     * @param TmecService $tmecService
     * @param string $id
     * 
     * @return Response
     */
    public function getTmecAction(Request $request, TmecService $tmecService, $id)
    {
        $tmec = $tmecService->getTmec($id);
        return $this->renderResponse($tmec);
    }

    /**
     * @Route("/delete/{id}", methods="DELETE")
     *
     * @param Request $request
     * @param TmecService $tmecService
     * @param string $id
     *
     * @return Response
     */
    public function delete(Request $request, TmecService $tmecService, string $id)
    {
        // Only super Admin can do this
        $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);

        $tmecService->delete($id);

        return $this->renderResponse(null, Response::HTTP_OK);
    }

    /**
     * @Route("/all", methods="GET")
     *
     * @param Request $request
     * @param TmecService $tmecService
     *
     * @return Response
     */
    public function getAllApplicationsAction(Request $request, TmecService $tmecService)
    {
        $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);

        $applications = $tmecService->getApplications();

        return $this->renderResponse($applications, Response::HTTP_OK, ["Default"]);
    }

}
