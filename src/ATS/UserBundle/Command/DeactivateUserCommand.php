<?php declare(strict_types=1);

namespace ATS\UserBundle\Command;

use ATS\UserBundle\Command\UserManagementBaseCommand;
use ATS\UserBundle\Manager\UserManager;

class DeactivateUserCommand extends UserManagementBaseCommand
{
    public function __construct(UserManager $userManager)
    {
        $this->cmdOperation = "deactivate";

        parent::__construct($userManager);
    }

    protected function configure()
    {
        parent::configure();
        $this->setDescription('Deactivates a user')
        ;
    }

    protected function doExecute()
    {
        $username = $this->input->getArgument(self::ARGUMENT_USERNAME);
        $user = $this->userManager->getUserByUsername($username);

        if ($user) {
            $user->deactivate();
            $this->userManager->update($user);
            $this->output->writeln("User $username has been deactivated");
        } else {
            $this->output->writeln("Unable to find user $username");
        }
    }
}
