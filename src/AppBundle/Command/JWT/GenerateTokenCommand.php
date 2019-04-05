<?php

namespace AppBundle\Command\JWT;

use AppBundle\Document\User;
use AppBundle\Manager\UserManager;
use AppBundle\Service\JWTHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTokenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("leadwire:jwt:generate")
            ->setDescription("Generates a JWT authenticationToken based on username & index")
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('index', InputArgument::OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var JwtHelper $jwt */
        $jwt = $this->getContainer()->get(JWTHelper::class);

        /** @var string $username */
        $username = $input->getArgument('username');
        /** @var ?string $index */
        $index = $input->getArgument('index');

        if ($index === null) {
            $user = $this->getContainer()->get(UserManager::class)->getOneBy(['username' => $username]);

            if ($user instanceof User) {
                $token = $jwt->encode($user->getUsername(), $user->getUserIndex());
                $output->writeln($token);
            } else {
                throw new \Exception("User $username not found");
            }
        } else {
            $token = $jwt->encode($username, $index);
            $output->writeln($token);
        }
    }
}
