<?php declare(strict_types=1);

namespace ATS\DistributionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BootstrapDevCommand extends ContainerAwareCommand
{
    /**
     * @var array
     */
    protected static $options = array(
        'symfony-web-dir' => 'web',
        'distribution-web-dir' => 'web',
    );

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('ats:dist:bootstrap:dev')
            ->setDescription('Bootstrap dev env by adding app_dev.php and config.php files')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem   = $this->getContainer()->get('filesystem');
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $symfonyWebDir = $rootDir . '/../' . $this->getSymfonyWebDir();

        $distributionWebDir =
            $rootDir . '/../src/ATS/DistributionBundle/Resources/skeleton/' . $this->getDistributionWebDir();

        $io = new SymfonyStyle($input, $output);

        if ($filesystem->exists($symfonyWebDir) && $filesystem->exists($distributionWebDir)) {
            try {
                $filesystem->copy($distributionWebDir .'/app_dev.php.dist', $symfonyWebDir .'/app_dev.php', true);
                $io->comment(sprintf('File "%s" bootstrapped.', 'app_dev.php'));
                $filesystem->copy($distributionWebDir .'/config.php.dist', $symfonyWebDir .'/config.php', true);
                $io->comment(sprintf('File "%s" bootstrapped.', 'config.php'));
            } catch (IOExceptionInterface $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }
            $io->success('Dev environment successfully bootstrapped.');
        } else {
            $io->error('Unable to bootstrap dev environment. Check your options.');
        }
    }

    /**
     * Get options
     *
     * @return array
     */
    protected static function getOptions()
    {
        $options = static::$options;

        return $options;
    }

    /**
     * Get symfony web directory
     *
     * @return string
     */
    protected function getSymfonyWebDir()
    {
        $options = static::getOptions();

        return $options['symfony-web-dir'];
    }

    /**
     * Get distribution web directory
     *
     * @return string
     */
    protected function getDistributionWebDir()
    {
        $options = static::getOptions();

        return $options['distribution-web-dir'];
    }
}
