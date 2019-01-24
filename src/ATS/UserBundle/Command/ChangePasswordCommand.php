<?php declare (strict_types = 1);

namespace ATS\UserBundle\Command;

use ATS\UserBundle\Command\UserManagementBaseCommand;
use ATS\UserBundle\Document\User;
use ATS\UserBundle\Manager\UserManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class ChangePasswordCommand extends UserManagementBaseCommand
{

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    public function __construct(UserManager $userManager, EncoderFactoryInterface $encoderFactory)
    {
        $this->cmdOperation = 'change-password';

        $this->encoderFactory = $encoderFactory;

        parent::__construct($userManager);
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Changes a user\'s password')
            ->addArgument(self::ARGUMENT_PASSWORD, null, 'The new password')
            ->addOption(
                'force-new-salt',
                null,
                InputOption::VALUE_NONE,
                'Generates a new salt value before setting the new password'
            )
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getHelper('question');

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
        $forceNewSalt = $this->input->getOption('force-new-salt');

        $encoder = $this->encoderFactory->getEncoder(User::class);

        $user = $this->userManager->getUserByUsername($username);

        if ($user) {
            if ($forceNewSalt) {
                $salt = $this->generateSalt();
            } else {
                $salt = $user->getSalt();
            }

            $user->setPassword($encoder->encodePassword($password, $salt));

            $this->userManager->update($user);
            $this->output->writeln("Password for user $username successfully modified");
        } else {
            $this->output->writeln("Unable to find user $username");
        }
    }
}
