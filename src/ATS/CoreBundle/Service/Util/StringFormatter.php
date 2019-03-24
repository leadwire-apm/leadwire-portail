<?php declare(strict_types=1);

namespace ATS\CoreBundle\Service\Util;

class StringFormatter
{

    /**
     * @param int|float $millis
     *
     * @return string
     */
    public static function humanizeDuration($millis)
    {
        $hours = floor($millis / 1000 / 60 / 60);
        $minutes = floor(($millis / 1000 / 60) - ($hours * 60));
        $seconds = floor(($millis / 1000) - $minutes * 60 - $hours * 60 * 60);

        $hours = $hours < 10 ? "0".$hours : $hours;
        $minutes = $minutes < 10 ? "0".$minutes : $minutes;
        $seconds = $seconds < 10 ? "0".$seconds : $seconds;

        return "{$hours}:{$minutes}:{$seconds}";
    }

    /**
     * @param int $memory
     *
     * @return string
     */
    public static function humanizeMemorySize($memory)
    {
        $units = ['B', 'kB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($memory >= 1024) {
            $memory /= 1024;
            $unitIndex++;
        }

        return "{$memory}{$units[$unitIndex]}";
    }
}
