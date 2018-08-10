<?php declare(strict_types=1);

namespace ATS\CoreBundle\Service\Exporter;

interface FormatterInterface
{
    /**
     *
     * @param array $data
     * @return string
     */
    public function format(array $data);
}
