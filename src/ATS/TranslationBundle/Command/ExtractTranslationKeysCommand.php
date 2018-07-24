<?php declare(strict_types=1);

namespace ATS\TranslationBundle\Command;

use ATS\CoreBundle\Command\Base\BaseCommand;
use ATS\TranslationBundle\Document\TranslationEntry;
use ATS\TranslationBundle\Manager\TranslationEntryManager;
use ATS\TranslationBundle\Service\TranslationKeysExtractor;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;

class ExtractTranslationKeysCommand extends BaseCommand
{
    private $extractor;
    private $manager;

    public function __construct(TranslationKeysExtractor $extractor, TranslationEntryManager $manager)
    {
        $this->extractor = $extractor;
        $this->manager = $manager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('ats:translation:extract')
            ->setDescription('Extracts translation keys from TWIG files')
            ->addOption('dir', 'd', InputOption::VALUE_REQUIRED, "Relative path for lookup directory")
            ->addOption('sync', 's', InputOption::VALUE_NONE, "Inserts missing keys in the database")
        ;
    }

    protected function doExecute()
    {
        $lookupDir = $this->input->getOption('dir');
        $sync = $this->input->getOption('sync');

        if (!$lookupDir) {
            $lookupDir = './src';
        }

        $translationKeys = [];

        $finder = new Finder();
        $twigFiles = $finder->files()->name('*.twig')->in($lookupDir);

        foreach ($twigFiles as $file) {
            $fileKeys = $this->extractor->extract($file->getRealPath());
            $translationKeys = array_merge($translationKeys, $fileKeys);
            $this->output->writeln($file->getRealPath());
            $this->output->writeln(implode("\n", $fileKeys));
        }

        if ($sync) {
            $this->output->writeln('Syncing new entries with the DB');

            $translationEntries = $this->manager->getAll();
            $availableKeys = array_map(
                function ($entry) {
                    return $entry->getKey();
                },
                $translationEntries
            );

            $newEntries = [];
            foreach ($translationKeys as $newKey) {
                if (!in_array($newKey, $availableKeys)) {
                    $this->output->writeln($newKey);
                    $newEntries[] = new TranslationEntry($newKey);
                }
            }

            $this->manager->batchUpdate($newEntries);
        }
    }
}
