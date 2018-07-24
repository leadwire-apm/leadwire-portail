<?php declare(strict_types=1);

namespace ATS\GeneratorBundle\Twig;

class GeneratorExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('ucfirst', array($this, 'capitalizeFirst')),
            new \Twig_SimpleFilter('lcfirst', array($this, 'lowerFirst')),
            new \Twig_SimpleFilter('parametersList', array($this, 'parametersList')),
        );
    }

    public function capitalizeFirst($str)
    {
        return ucfirst($str);
    }

    public function lowerFirst($str)
    {
        return lcfirst($str);
    }

    public function parametersList($fields)
    {
        $paramsList = '';

        foreach ($fields as $field) {
            $paramsList .= $field['shortType'] . ' $' . $field['fieldName'] . ', ';
        }

        return substr($paramsList, 0, strlen($paramsList)-2);
    }
}
