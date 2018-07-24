<?php declare(strict_types=1);

namespace ATS\CoreBundle\Service\Parser;

use ATS\CoreBundle\Exception\InvalidParserTypeException;

class Parser
{
    const XML_TYPE = 'xml';
    const JSON_TYPE = 'json';
    const CSV_TYPE = 'csv';
    const XLS_TYPE = 'xls';
    const XLSX_TYPE = 'xlsx';

    public function __construct()
    {
    }

    /**
     * @param array  $data
     * @param string $type
     * @param array  $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function parse($data = array(), $type = self::JSON_TYPE, $options = array())
    {
        $strategy = self::resolveStrategyFromType($type);

        return $strategy->doParse($data, $options);
    }

    /**
     * @param string $type
     *
     * @return ParserStrategyInterface
     *
     * @throws InvalidParserTypeException
     */
    private function resolveStrategyFromType($type)
    {
        $strategy = null;

        if ($type == self::JSON_TYPE) {
            $strategy = new JsonStrategy();
        } elseif ($type == self::CSV_TYPE) {
            $strategy = new CsvStrategy();
        } else {
            $message = "Unable to parse format ($type)\n";
            $message .= "Available types are: json, csv";
            throw new InvalidParserTypeException($message);
        }

        return $strategy;
    }
}
