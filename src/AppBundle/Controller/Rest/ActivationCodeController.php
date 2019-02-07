<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\ActivationCodeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ActivationCodeController extends Controller
{

    /**
     * @Route("/new/{sendEmail}", methods="POST", defaults={"send-email"=false})
     *
     * @param Request $request
     * @param ActivationCodeService $acs
     * @param bool $sendEmail
     *
     * @return Response
     */
    public function newActivationCodeAction(Request $request, ActivationCodeService $acs, bool $sendEmail)
    {
        if ($sendEmail === false) {
            return new JsonResponse(['code' => $acs->generateNewCode()->getCode()]);
        } else {
            ;// TODO
        }
    }
}
