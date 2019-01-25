<?php declare (strict_types = 1);

namespace ATS\EmailBundle\Controller\Rest;

use ATS\EmailBundle\Document\Email;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ATS\EmailBundle\Service\SimpleMailerService;
use Symfony\Component\Routing\Annotation\Route;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;

class EmailController extends Controller
{
    use RestControllerTrait;

    /**
     * @Route("/send/{doSave}", methods="POST", defaults={"doSave"=false})
     *
     * @param Request $request
     * @param bool $doSave
     * @param SerializerInterface $serializer
     * @param SimpleMailerService $mailer
     *
     * @return Response
     */
    public function sendEmailAction(
        Request $request,
        $doSave,
        SerializerInterface $serializer,
        SimpleMailerService $mailer
    ) {
        $response = false;

        $email = $serializer->deserialize($request->getContent('email'), Email::class, 'json');

        if ($email) {
            $response = $mailer->send($email, $doSave);
        }

        return $this->renderResponse([$response]);
    }

    /**
     * @Route("/resend/", methods="POST")
     *
     * @param Request $request
     * @param SimpleMailerService $mailer
     *
     * @return Response
     */
    public function resendEmailAction(Request $request, SimpleMailerService $mailer)
    {
        $id = json_decode($request->getContent('id'));

        $mailer->resend($id);

        return $this->renderResponse([]);
    }
}
