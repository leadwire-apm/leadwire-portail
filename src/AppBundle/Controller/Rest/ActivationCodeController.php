<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\User;
use AppBundle\Service\ActivationCodeService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivationCodeController extends Controller
{

    /**
     * @Route("/new/{sendEmail}", methods="POST", defaults={"sendEmail"=false})
     *
     * @param Request $request
     * @param ActivationCodeService $acs
     * @param bool $sendEmail
     *
     * @return Response
     */
    public function newActivationCodeAction(Request $request, ActivationCodeService $acs, bool $sendEmail)
    {
        $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);

        if ($sendEmail === false) {
            // Only super Admin can do this
            $this->denyAccessUnlessGranted([User::ROLE_SUPER_ADMIN]);

            return new JsonResponse(['code' => $acs->generateNewCode()->getCode()]);
        } else {
            $activationCode = $acs->generateNewCode();
            // TODO: Send Email
            // ? To which user
        }
    }
}
