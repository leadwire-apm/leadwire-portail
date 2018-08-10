<?php declare(strict_types=1);

namespace ATS\CoreBundle\Service\Parser;

class JsonStrategy implements ParserStrategyInterface
{
    public function __construct()
    {
    }

    public function doParse($data, $options = array())
    {
        return json_decode($data, true);
    }
}
