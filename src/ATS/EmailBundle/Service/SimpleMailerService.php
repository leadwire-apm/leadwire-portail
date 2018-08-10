<?php declare(strict_types=1);


namespace ATS\EmailBundle\Service;

use Twig\Template;
use Twig\Error\Error;
use Psr\Log\LoggerInterface;
use ATS\EmailBundle\Document\Email;
use ATS\EmailBundle\Manager\EmailManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class SimpleMailerService
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EngineInterface
     */
    private $templatingEngine;

    /**
     * @var EmailManager
     */
    private $emailManager;

    /**
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $templatingEngine
     * @param EmailManager $emailManager
     */
    public function __construct(
        \Swift_Mailer $mailer,
        EngineInterface $templatingEngine,
        EmailManager $emailManager
    ) {
        $this->mailer = $mailer;
        $this->templatingEngine = $templatingEngine;
        $this->emailManager = $emailManager;
    }

    /**
     * Sends an email with SwiftMailer
     *
     * @param Email $email
     * @param bool $doSave
     *
     * @return int
     */
    public function send(Email $email, $doSave = false)
    {
        $message = (new \Swift_Message())
            ->setSubject($email->getSubject())
            ->setFrom($email->getSenderAddress(), $email->getSenderName())
            ->setTo($email->getRecipientAddress())
            ->setBody(
                $this->render($email->getTemplate(), $email->getMessageParameters()),
                'text/html'
            );

        $sentNumber = $this->mailer->send($message);

        if ($doSave) {
            $this->emailManager->update($email);
        }

        return $sentNumber;
    }

    /**
     * @param \MongoId $id
     *
     * @return int
     */
    public function resend($id)
    {
        $email = $this->emailManager->getOneBy(['id' => $id]);

        if ($email) {
            return $this->send($email);
        }

        return -1;
    }
    /**
     * Renders
     * @param string $templateName
     * @param array  $parameters
     *
     * @return null|Template
     */
    protected function render($templateName, $parameters)
    {
        try {
            return $this->templatingEngine->render($templateName, $parameters);
        } catch (Error $e) {
            return null;
        }
    }
}
