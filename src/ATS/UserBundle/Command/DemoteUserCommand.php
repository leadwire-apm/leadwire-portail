<?php declare(strict_types=1);

namespace ATS\UserBundle\Command;

use ATS\UserBundle\Document\User;
use ATS\UserBundle\Manager\UserManager;
use ATS\UserBundle\Command\UserManagementBaseCommand;

class DemoteUserCommand extends UserManagementBaseCommand
{
    public function __construct(UserManager $userManager)
    {
        $this->cmdOperation = "demote";

        parent::__construct($userManager);
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Revoke roles to a user')
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
                $user->revoke($role);
                $this->output->writeln("role [$role] revoked");
            }

            $this->userManager->update($user);
        } else {
            $this->output->writeln("Unable to find user $username");
        }
    }
}
