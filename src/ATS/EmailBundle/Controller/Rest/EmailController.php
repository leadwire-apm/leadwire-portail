<?php declare (strict_types = 1);

namespace ATS\EmailBundle\Controller\Rest;

use ATS\CoreBundle\Controller\Rest\BaseRestController;
use ATS\EmailBundle\Document\Email;
use ATS\EmailBundle\Service\SimpleMailerService;
use FOS\RestBundle\Controller\Annotations\Route;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailController extends BaseRestController
{
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

        return $this->prepareJsonResponse([$response]);
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

        return $this->prepareJsonResponse([]);
    }
}
