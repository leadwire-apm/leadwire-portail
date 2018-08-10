<?php declare(strict_types=1);

namespace ATS\CoreBundle\Service\Parser;

interface ParserStrategyInterface
{
    /**
     * @param string $data
     * @param array  $options
     * @return mixed
     */
    public function doParse($data, $options = array());
}
