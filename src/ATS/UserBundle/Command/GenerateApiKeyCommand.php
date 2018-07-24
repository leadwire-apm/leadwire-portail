<?php declare(strict_types=1);

namespace ATS\UserBundle\Command;

use ATS\CoreBundle\Service\Util\StringWrapper;
use ATS\UserBundle\Command\UserManagementBaseCommand;
use ATS\UserBundle\Manager\UserManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class GenerateApiKeyCommand extends UserManagementBaseCommand
{
    const API_KEY_DEFAULT_LENGTH = 32;

    public function __construct(UserManager $userManager)
    {
        $this->cmdOperation = "generate:api:key";

        parent::__construct($userManager);
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Generates a new API key for a given User instance')
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
    }

    protected function doExecute()
    {
        $username = $this->input->getArgument(self::ARGUMENT_USERNAME);

        $user = $this->userManager->getUserByUsername($username);

        if ($user) {
            $user->setApiKey(StringWrapper::random(self::API_KEY_DEFAULT_LENGTH));
            $this->userManager->update($user);
        } else {
            $this->output->writeln("Unable to find user $username");
        }
    }
}
