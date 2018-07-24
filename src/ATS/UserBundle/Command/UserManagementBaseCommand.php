<?php declare(strict_types=1);

namespace ATS\UserBundle\Command;

use ATS\UserBundle\Document\User;
use ATS\UserBundle\Manager\UserManager;
use ATS\CoreBundle\Command\Base\BaseCommand;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class UserManagementBaseCommand extends BaseCommand
{
    protected $userManager;
    protected $cmdOperation = '';

    const ARGUMENT_USERNAME = 'username';
    const ARGUMENT_PASSWORD = 'password';
    const ARGUMENT_ROLES = 'roles';

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName("ats:user:$this->cmdOperation")
             ->addArgument(self::ARGUMENT_USERNAME, null, 'The User\'s username')
             ;
    }

    protected function generateSalt()
    {
        return base64_encode(random_bytes(30));
    }
}
