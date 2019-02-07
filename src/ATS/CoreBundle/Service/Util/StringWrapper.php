<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Service\Util;

class StringWrapper
{
    /**
     * @var string
     */
    private $str;

    /**
     * @param $str
     */
    public function __construct($str = '')
    {
        $this->str = $str;
    }

    /**
     * @param $subStr
     * @param $caseSensitive
     */
    public function startsWith($subStr, $caseSensitive = true)
    {
        $str = $caseSensitive ? strtoupper($this->str) : $this->str;
        $subStr = $caseSensitive ? strtoupper($subStr) : $subStr;

        $length = strlen($subStr);

        return (substr($str, 0, $length) === $subStr);
    }

    /**
     * @param $subStr
     * @param $caseSensitive
     * @return mixed
     */
    public function endsWith($subStr, $caseSensitive = true)
    {
        $str = $caseSensitive ? strtoupper($this->str) : $this->str;
        $subStr = $caseSensitive ? strtoupper($subStr) : $subStr;

        $length = strlen($subStr);

        return $length === 0 || (substr($str, -$length) === $subStr);
    }

    /**
     * @param $subStr
     * @param $caseSensitive
     */
    public function contains($subStr, $caseSensitive = true)
    {
        $str = $caseSensitive ? strtoupper($this->str) : $this->str;
        $subStr = $caseSensitive ? strtoupper($subStr) : $subStr;

        return strpos($str, $subStr) !== false;
    }

    /**
     * @param $subject
     * @return mixed
     */
    public static function stringify($subject)
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

    /**
     * Generates random string with length $length
     *
     * @param int $length
     *
     * @return string
     */
    public static function random($length)
    {
        $alphaNum = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $random = '';

        for ($i = 0; $i < $length; $i++) {
            $random .= $alphaNum[mt_rand(0, count($alphaNum) - 1)];
        }

        return $random;
    }
}
