<?php declare (strict_types = 1);

namespace ATS\GeneratorBundle\Generator;

use ATS\CoreBundle\Service\Util\StringWrapper;
use Symfony\Component\DependencyInjection\Container;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityViewGenerator extends Generator
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

    public function generate(BundleInterface $bundle, $viewEntity, $fields)
    {
        $dir = $bundle->getPath();
        $entityFile = $dir . '/ViewEntity/' . ucfirst($viewEntity) . 'View.php';
        $controllerFile = $dir . '/Controller/View/' . ucfirst($viewEntity) . 'ViewController.php';

        if (file_exists($entityFile)) {
            unlink($entityFile);
        }

        if (file_exists($controllerFile)) {
            unlink($controllerFile);
        }

        $parameters = array(
            'namespace' => $bundle->getNamespace(),
            'bundle' => $bundle->getName(),
            'viewEntity' => $viewEntity,
            'fields' => $fields,
        );

        $this->renderFile('entity.php.twig', $entityFile, $parameters);

        $parameters = array(
            'namespace' => $bundle->getNamespace(),
            'bundle' => $bundle->getName(),
            'viewEntity' => $viewEntity,
        );

        $this->renderFile('viewController.php.twig', $controllerFile, $parameters);

        $this->generateRouting($bundle, $viewEntity."ViewController");
    }

    public function generateRouting(BundleInterface $bundle, $controller)
    {
        $content = '';

        $file = $bundle->getPath() . '/Resources/config/routing.yml';
        if (file_exists($file)) {
            $content = file_get_contents($file);
        } elseif (!is_dir($dir = $bundle->getPath() . '/Resources/config')) {
            self::mkdir($dir);
        }

        $format =
            "\n%s:\n    resource: \"@%s/Controller/View/%s.php\"\n" .
            "    type: \"rest\"\n    prefix: \"/%%internal_api_prefix%%/%s\"\n    options:\n        expose: true\n";

        if (!(new StringWrapper($content))->contains(
            sprintf(
                $format,
                strtolower(
                    substr($bundle->getName(), 0, strlen($bundle->getName()) - 6)
                ) . "_rest_" . strtolower($controller),
                $bundle->getName(),
                $controller,
                strtolower($controller)
            )
        )
        ) {
            $content .=
            sprintf(
                $format,
                strtolower(
                    substr($bundle->getName(), 0, strlen($bundle->getName()) - 6)
                ) . "_rest_" . strtolower($controller),
                $bundle->getName(),
                $controller,
                strtolower($controller)
            );
        }

        $flink = fopen($file, 'w');
        if ($flink) {
            $write = fwrite($flink, $content);

            if ($write) {
                fclose($flink);
            } else {
                throw new \RuntimeException(
                    sprintf(
                        'We cannot write into file "%s", has that file the correct access level?',
                        $file
                    )
                );
            }
        } else {
            throw new \RuntimeException(
                sprintf(
                    'Problems with generating file "%s", did you gave write access to that directory?',
                    $file
                )
            );
        }
    }
}
