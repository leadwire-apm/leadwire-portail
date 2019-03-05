<?php

namespace AppBundle\Command;

use AppBundle\Service\JWTHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTokenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("leadwire:jwt:generate:token")
            ->setDescription("Generates a JWT authenticationToken based on username & index")
            ->addArgument('username')
            ->addArgument('index');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var JwtHelper $jwt */
        $jwt = $this->getContainer()->get(JWTHelper::class);

        /** @var string $username */
        $username = $input->getArgument('username');
        /** @var string $index */
        $index = $input->getArgument('index');


        $token = $jwt->encode($username, $index);

        $output->writeln($token);
    }
}
