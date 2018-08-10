<?php declare(strict_types=1);

namespace ATS\CoreBundle\Exception;

class InvalidParserTypeException extends \Exception
{
    public function __construct($message = '')
    {
        parent::__construct($message);
    }
}
