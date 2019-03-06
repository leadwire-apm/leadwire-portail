<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Service\Util;

final class AString implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var string
     */
    private $str;

    /**
     * @param string $str
     */
    public function __construct(string $str = '')
    {
        $this->str = $str;
    }

    public function length(): int
    {
        return mb_strlen($this->str);
    }

    /**
     * returns the position of $subStr within the current string
     *
     * @param string $subStr
     *
     * @return integer
     */
    public function position(string $subStr): int
    {
        $result = mb_strpos($this->str, $subStr);

        if ($result === false) {
            return -1;
        } else {
            return $result;
        }
    }

    /**
     * @param string $subStr
     * @param bool $caseSensitive
     *
     * @return bool
     */
    public function startsWith($subStr, $caseSensitive = true): bool
    {
        $str = $caseSensitive === true ? mb_strtoupper($this->str) : $this->str;
        $subStr = $caseSensitive === true ? mb_strtoupper($subStr) : $subStr;
        $length = mb_strlen($subStr);

        return mb_substr($str, 0, $length) === $subStr;
    }

    /**
     * @param string $subStr
     * @param bool $caseSensitive
     *
     * @return bool
     */
    public function endsWith($subStr, $caseSensitive = true): bool
    {
        $str = $caseSensitive === true ? mb_strtoupper($this->str) : $this->str;
        $subStr = $caseSensitive === true ? mb_strtoupper($subStr) : $subStr;
        $length = mb_strlen($subStr);

        return ($length === 0) || (mb_substr($str, -$length) === $subStr);
    }

    /**
     * @param string $subStr
     * @param bool $caseSensitive
     *
     * @return bool
     */
    public function contains($subStr, $caseSensitive = true): bool
    {
        $str = $caseSensitive === true ? mb_strtoupper($this->str) : $this->str;
        $subStr = $caseSensitive === true ? mb_strtoupper($subStr) : $subStr;

        return mb_strpos($str, $subStr) !== false;
    }

    /**
     * Capitalize each word of the string
     *
     * @return self
     */
    public function capitalize(): self
    {
        $this->str = mb_convert_case($this->str, MB_CASE_TITLE, 'UTF-8');

        return $this;
    }

    /**
     * Returns UPPER-CASE string representation
     *
     * @return self
     */
    public function toUpper(): self
    {
        $this->str = mb_convert_case($this->str, MB_CASE_UPPER, 'UTF-8');

        return $this;
    }

    /**
     * Returns lower-case string representation
     *
     * @return self
     */
    public function toLower(): self
    {
        $this->str = mb_convert_case($this->str, MB_CASE_LOWER, 'UTF-8');

        return $this;
    }

    /**
     * Returns the right side of $subStr
     *
     * @param string $subStr
     *
     * @return self
     */
    public function rightOf(string $subStr): self
    {
        if ($subStr === '') {
            return new AString($this->str);
        }

        if ($this->contains($subStr) === true) {
            return $this->split($subStr)[1];
        }

        return new AString();
    }

    /**
     * Returns the left side of $subStr
     *
     * @param string $subStr
     *
     * @return self
     */
    public function leftOf(string $subStr): self
    {
        if ($subStr === '') {
            return new AString($this->str);
        }

        if ($this->contains($subStr) === true) {
            return $this->split($subStr)[0];
        }

        return new AString();
    }

    /**
     * Trims leading and tailing whitespaces
     *
     * @return self
     */
    public function trim(): self
    {
        $this->str = trim($this->str);

        return $this;
    }

    /**
     * Splits a string using $separator
     *
     * @param string $separator
     *
     * @return array
     */
    public function split(string $separator): array
    {
        $arr = mb_split($separator, $this->str);

        $split = array_map(
            function (string $element) {
                return new AString($element);
            },
            $arr
        );

        return $split;
    }

    /**
     * Returns the revers of the string
     *
     * @return self
     */
    public function reverse(): self
    {
        $reversed = '';

        for ($i = mb_strlen($this->str); $i >= 0; $i--) {
            $reversed .= mb_substr($this->str, $i, 1);
        }

        $this->str = $reversed;

        return $this;
    }

    /**
     * @param mixed $subject
     *
     * @return string
     */
    public static function stringify($subject): string
    {
        try {
            if (is_bool($subject) === true) {
                return $subject === true ? 'True' : 'False';
            }
            if ($subject instanceof \DateTime) {
                return $subject->format('Y-m-d');
            }

            if (is_array($subject) === true) {
                return implode('|', $subject);
            }

            return (string) $subject;
        } catch (\Exception $e) {
            if (method_exists($subject, 'getId') === true) {
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
    public static function random($length): string
    {
        $alphaNum = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'));
        $random = '';

        for ($i = 0; $i < $length; $i++) {
            $random .= $alphaNum[mt_rand(0, count($alphaNum) - 1)];
        }

        return $random;
    }

    /**
     * Returns an array consisting of the characters in the string.
     *
     * @return array An array of string chars
     */
    public function asArray()
    {
        $chars = [];
        for ($i = 0, $l = mb_strlen($this->str); $i < $l; $i++) {
            $chars[] = $this->str[$i];
        }
        return $chars;
    }

    /**
     * Cast to plain string object
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->str;
    }

    /**
     * Returns whether or not a character exists at an index. Offsets may be
     * negative to count from the last character in the string. Implements
     * part of the ArrayAccess interface.
     *
     * @param  mixed   $offset The index to check
     * @return boolean Whether or not the index exists
     */
    public function offsetExists($offset)
    {
        $length = $this->length();
        $offset = (int) $offset;
        if ($offset >= 0) {
            return ($length > $offset);
        }

        return ($length >= abs($offset));
    }

    /**
     * Returns the character at the given index. Offsets may be negative to
     * count from the last character in the string. Implements part of the
     * ArrayAccess interface, and throws an OutOfBoundsException if the index
     * does not exist.
     *
     * @param  mixed $offset         The index from which to retrieve the char
     * @return mixed                 The character at the specified index
     * @throws \OutOfBoundsException If the positive or negative offset does
     *                               not exist
     */
    public function offsetGet($offset)
    {
        $offset = (int) $offset;
        $length = $this->length();

        if (($offset >= 0 && $length <= $offset) || $length < abs($offset)) {
            throw new \OutOfBoundsException('No character exists at the index');
        }

        // Negative offset -> get from the last position
        if ($offset < 0) {
            $offset = $length + $offset;
        }

        return mb_substr($this->str, $offset, 1);
    }

    /**
     * Implements part of the ArrayAccess interface, but throws an exception
     * when called. This maintains the immutability of objects.
     *
     * @param  mixed      $offset The index of the character
     * @param  mixed      $value  Value to set
     * @throws \Exception When called
     */
    public function offsetSet($offset, $value)
    {
        // Don't allow directly modifying the string
        throw new \Exception('AString object is immutable, cannot modify char');
    }

    /**
     * Implements part of the ArrayAccess interface, but throws an exception
     * when called. This maintains the immutability of objects.
     *
     * @param  mixed      $offset The index of the character
     * @throws \Exception When called
     */
    public function offsetUnset($offset)
    {
        // Don't allow directly modifying the string
        throw new \Exception('AString object is immutable, cannot unset char');
    }

    /**
     * Returns a new ArrayIterator, thus implementing the IteratorAggregate
     * interface. The ArrayIterator's constructor is passed an array of chars
     * in the multibyte string. This enables the use of foreach with instances
     * of Stringy\Stringy.
     *
     * @return \ArrayIterator An iterator for the characters in the string
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->asArray());
    }

    /**
     *
     * @param string $str
     *
     * @return self
     */
    public function append(string $str): self
    {
        $this->str = $this->str . $str;

        return $this;
    }

    /**
     *
     * @param string $str
     *
     * @return self
     */
    public function prepend(string $str): self
    {
        $this->str = $str . $this->str;

        return $this;
    }

    /**
     *
     * @param string $pattern
     * @param string $replacement
     * @param integer $limit
     *
     * @return self
     */
    public function replace(string $pattern, string $replacement, int $limit = 1): self
    {
        $this->str = str_replace($pattern, $replacement, $this->str, $limit);

        return $this;
    }

    public function indent(int $level = 1, string $char = "\t"): self
    {
        return $this;
    }
}
