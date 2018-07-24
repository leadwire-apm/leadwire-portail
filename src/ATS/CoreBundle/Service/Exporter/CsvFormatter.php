<?php declare(strict_types=1);

namespace ATS\CoreBundle\Service\Exporter;

use ATS\CoreBundle\Service\Exporter\FormatterInterface;

class CsvFormatter implements FormatterInterface
{
    public function format(array $data)
    {
        return implode("\n", array_map(function ($line) {
            return implode(";", $line);
        }, $data));
    }
}
