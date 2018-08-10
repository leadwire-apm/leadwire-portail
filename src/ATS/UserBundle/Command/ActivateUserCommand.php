<?php declare(strict_types=1);

namespace ATS\UserBundle\Command;

use ATS\UserBundle\Command\UserManagementBaseCommand;
use ATS\UserBundle\Manager\UserManager;

class ActivateUserCommand extends UserManagementBaseCommand
{
    public function __construct(UserManager $userManager)
    {
        $this->cmdOperation = "activate";

        parent::__construct($userManager);
    }

    protected function configure()
    {
        parent::configure();
        $this->setDescription('Activates a user')
        ;
    }

    protected function doExecute()
    {
        $username = $this->input->getArgument(self::ARGUMENT_USERNAME);
        $user = $this->userManager->getUserByUsername($username);

        if ($user) {
            $user->activate();
            $this->userManager->update($user);
            $this->output->writeln("<info>user $username has been activated.</info>");
        } else {
            $this->output->writeln("<error>Unable to find user $username</error>");
        }
    }
}
