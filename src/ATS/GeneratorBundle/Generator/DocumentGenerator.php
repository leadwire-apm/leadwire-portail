<?php declare(strict_types=1);

namespace ATS\GeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class DocumentGenerator extends Generator
{

    protected $skeletonDirs;
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setSkeletonDirs($skeletonDirs)
    {
        $this->skeletonDirs = is_array($skeletonDirs) ? $skeletonDirs : array($skeletonDirs);
    }

    protected function getTwigEnvironment()
    {
        $twig = clone $this->container->get('twig');
        $twig->setLoader(new \Twig_Loader_Filesystem($this->skeletonDirs));

        return $twig;
    }

    public function generate(BundleInterface $bundle, $document, $fields)
    {
        $dir = $bundle->getPath();
        $documentFile = $dir.'/Document/'.ucfirst($document).'.php';
        $repositoryFile = $dir.'/Repository/'.ucfirst($document).'Repository.php';
        $managerFile = $dir.'/Manager/'.ucfirst($document).'Manager.php';
        $serviceFile = $dir.'/Service/'.ucfirst($document).'Service.php';

        if (file_exists($documentFile)) {
            unlink($documentFile);
        }

        $usesArrayCollection = false;
        foreach ($fields as $field) {
            if ($field['associationMany'] == true) {
                $usesArrayCollection = true;
                break;
            }
        }

        $parameters = array(
            'namespace' => $bundle->getNamespace(),
            'bundle' => $bundle->getName(),
            'document' => $document,
            'fields' => $fields,
            'usesArrayCollection' => $usesArrayCollection
        );

        $this->renderFile('document.php.twig', $documentFile, $parameters);

        if (file_exists($repositoryFile)) {
            unlink($repositoryFile);
        }

        $this->renderFile('repository.php.twig', $repositoryFile, $parameters);

        if (file_exists($managerFile)) {
            unlink($managerFile);
        }

        $this->renderFile('manager.php.twig', $managerFile, $parameters);

        if ($serviceFile) {
            if (file_exists($serviceFile)) {
                unlink($serviceFile);
            }

            $this->renderFile('service.php.twig', $serviceFile, $parameters);
        }
    }
}
