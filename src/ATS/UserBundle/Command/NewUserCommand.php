<?php declare(strict_types=1);

namespace ATS\UserBundle\Command;

use ATS\UserBundle\Command\UserManagementBaseCommand;
use ATS\UserBundle\Document\User;
use ATS\UserBundle\Manager\UserManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class NewUserCommand extends UserManagementBaseCommand
{
    private $encoderFactory;

    public function __construct(UserManager $userManager, EncoderFactoryInterface $encoderFactory)
    {
        $this->cmdOperation = "create";
        $this->encoderFactory = $encoderFactory;

        parent::__construct($userManager);
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Creates a new User instance')
            ->addArgument(self::ARGUMENT_PASSWORD, null, 'The new password')
            ->addArgument(self::ARGUMENT_ROLES, null, 'User roles (comma separated)', User::DEFAULT_ROLE)
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getHelper('question');

        if ($input->getArgument(self::ARGUMENT_USERNAME) == null) {
            $question = new Question('Username ?', null);
            $username = $questionHelper->ask($input, $output, $question);
            $input->setArgument(self::ARGUMENT_USERNAME, $username);
        }

        if ($input->getArgument(self::ARGUMENT_PASSWORD) == null) {
            $question = new Question('Password ?', null);
            $password = $questionHelper->ask($input, $output, $question);
            $input->setArgument(self::ARGUMENT_PASSWORD, $password);
        }
    }

    protected function doExecute()
    {
        $username = $this->input->getArgument(self::ARGUMENT_USERNAME);
        $password = $this->input->getArgument(self::ARGUMENT_PASSWORD);
        $roles = $this->input->getArgument(self::ARGUMENT_ROLES);
        $roles = explode(',', $roles);

        $encoder = $this->encoderFactory->getEncoder(User::class);

        $salt = $this->generateSalt();

        $user = $this->userManager->getUserByUsername($username);

        if (!$user) {
            $user = (new User())
                ->setUsername($username)
                ->setSalt($salt)
                ->setRoles($roles)
                ->setPassword($encoder->encodePassword($password, $salt));
            $this->userManager->update($user);
            $this->output->writeln("<info>User [$username] created successfully.</info>");
        } else {
            $this->output->writeln("A user with the username [$username] already exists !");
        }
    }
}
