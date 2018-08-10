<?php declare(strict_types=1);

namespace ATS\UserBundle\Command;

use ATS\UserBundle\Document\User;
use ATS\UserBundle\Manager\UserManager;
use ATS\UserBundle\Command\UserManagementBaseCommand;

class PromoteUserCommand extends UserManagementBaseCommand
{
    public function __construct(UserManager $userManager)
    {
        $this->cmdOperation = "promote";

        parent::__construct($userManager);
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Promotes a user to a specific role')
            ->addArgument(self::ARGUMENT_ROLES, null, 'User roles (comma separated)', User::DEFAULT_ROLE)
        ;
    }

    protected function doExecute()
    {
        $username = $this->input->getArgument(self::ARGUMENT_USERNAME);
        $roles = $this->input->getArgument(self::ARGUMENT_ROLES);
        $roles = explode(',', $roles);

        $user = $this->userManager->getUserByUsername($username);
        if ($user) {
            foreach ($roles as $role) {
                $user->promote($role);
                $this->output->writeln("Role [$role] added");
            }

            $this->userManager->update($user);
        } else {
            $this->output->writeln("Unable to find user $username");
        }
    }
}
