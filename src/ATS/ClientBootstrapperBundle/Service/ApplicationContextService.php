<?php declare (strict_types = 1);

namespace ATS\ClientBootstrapperBundle\Service;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Phramz\Doctrine\Annotation\Scanner\ClassFileInfo;

/**
 * Service class for Application Context
 *
 */
class ApplicationContextService
{
    /**
     * @param array $views
     * @return mixed
     */
    public function handleViews(array $views)
    {
        $result = [];

        foreach ($views as $view) {
            $classname = $view->getClassname();
            $shortClassname = strtolower(
                (new \ReflectionClass($classname))
                    ->getShortName()
            );
            $result['fields'][] = $shortClassname;
            $result['schema'][$shortClassname] = $this->handleView($view);

            $boilerplate = new $classname;
            $result['boilerplates'][$shortClassname] = $boilerplate;
        }

        return $result;
    }

    /**
     * @param ClassFileInfo $cfi
     * @return mixed
     */
    public function handleView(ClassFileInfo $cfi)
    {
        $result = [];

        $shortClassname = strtolower(
            (new \ReflectionClass($cfi->getClassName()))
                ->getShortName()
        );

        $annotationHolder = $cfi->getPropertyAnnotations();

        foreach ($annotationHolder as $field => $annotations) {
            foreach ($annotations as $annotation) {
                if ($annotation instanceof ODM\Id) {
                    continue 2;
                }
                if ($annotation instanceof JMS\Type) {
                    $result[$field] = $annotation->name;
                }
            }
        }

        return $result;
    }
}
