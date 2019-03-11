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
     * @Route("/list/{application}/{completed}", methods="GET")
     *
     * @param Request $request
     * @param TmecService $tmecService
     * @param string $application
     * @param string $completed
     * 
     * @return Response
     */
    public function listTmecAction(Request $request, TmecService $tmecService, $application, $completed)
    {
        $params = [ 'application' => $application, 'completed' => $completed];
        $tmec = $tmecService->listTmec($params);
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
     * @param tmecService $tmecService
     * @param string $id
     *
     * @return Response
     */
    public function delete(Request $request, tmecService $tmecService, string $id)
    {
        // Only super Admin can do this
        $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);

        $tmecService->delete($id);

        return $this->renderResponse(null, Response::HTTP_OK);
    }
}
