<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\User;
use AppBundle\Service\ActivationCodeService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;

class ActivationCodeController extends Controller
{
    use RestControllerTrait;
    /**
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param ActivationCodeService $acs
     *
     * @return Response
     */
    public function newActivationCodeAction(Request $request, ActivationCodeService $acs)
    {
        // Only super Admin can do this
        $this->denyAccessUnlessGranted([User::ROLE_SUPER_ADMIN]);

        return $this->renderResponse(['code' => $acs->generateNewCode()->getCode()]);
    }

    /**
     * @Route("/list", methods="GET")
     *
     * @param Request $request
     * @param ActivationCodeService $acs
     *
     * @return Response
     */
    public function listActivationCodeAction(Request $request, ActivationCodeService $acs)
    {
        // Only super Admin can do this
        $this->denyAccessUnlessGranted([User::ROLE_SUPER_ADMIN]);

        return $this->renderResponse($acs->listActivationCodes());
    }
}
