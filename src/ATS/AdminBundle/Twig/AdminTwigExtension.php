<?php declare(strict_types=1);

namespace ATS\AdminBundle\Twig;

class AdminTwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('to_string', array($this, 'toString')),
            new \Twig_SimpleFilter('to_array', array($this, 'objectToArray')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('is_reference', array($this, 'isReference')),
            new \Twig_SimpleFunction('get_reference_url', array($this, 'getReferenceURL')),
        );
    }

    public function toString($subject)
    {
        try {
            if (is_bool($subject)) {
                return $subject == true ? 'True' : 'False';
            }
            if ($subject instanceof \DateTime) {
                return $subject->format('Y-m-d');
            }

            if (is_array($subject)) {
                return implode('|', $subject);
            }

            if (is_object($subject)) {
                return (string) $subject;
            }

            return (string) $subject;
        } catch (\Exception $e) {
            if (method_exists($subject, 'getId')) {
                return $subject->getId();
            }

            return get_class($subject) . ' Object';
        }
    }

    public function objectToArray($subject)
    {
        $reflectionClass = new \ReflectionClass(get_class($subject));
        $array = array();
        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->getName() == 'id') {
                continue;
            }
            $property->setAccessible(true);
            $array[$property->getName()] = $property->getValue($subject);
            $property->setAccessible(false);
        }
        return $array;
    }

    /**
     * @TODO needs major rework
     *
     * @param Object $subject
     * @return bool
     */
    public function isReference($subject)
    {
        try {
            if (is_object($subject)) {
                if (method_exists($subject, 'getId')) {
                    return $subject->getId() != null;
                }

                return false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getReferenceURL($adminRoutesPrefix, $subject)
    {
        $source = \Doctrine\Common\Util\ClassUtils::getClass($subject);
        $source = strtolower(str_replace('\\', '/', $source));
        $path = sprintf("/%s/edit/%s/%s", $adminRoutesPrefix, $source, $subject->getId());

        return $path;
    }
}
