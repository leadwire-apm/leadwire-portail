<?php declare(strict_types=1);

namespace ATS\CoreBundle\Service\Parser;

class CsvStrategy implements ParserStrategyInterface
{
    const DELIMITER_OPTION = 'delimiter';
    const HAS_HEADER_OPTION = 'has_header';
    const EOL_CHAR_OPTION = 'eol_char';

    private $delimiter;

    private $hasHeaderLine;

    private $eolCharacter;


    public function __construct()
    {
        $this->reset();
    }

    /**
     * @inheritdoc
     */
    public function doParse($data, $options = array())
    {
        $parsed = [];

        $this->resolveOptions($options);

        $lines = explode($this->eolCharacter, $data);

        if ($this->hasHeaderLine) {
            array_shift($lines);
        }

        foreach ($lines as $line) {
            $parsed[] = explode($this->delimiter, $line);
        }

        return $parsed;
    }

    /**
     * @param array $options
     */
    private function resolveOptions($options)
    {
        $this->reset();

        $this->delimiter = array_key_exists(self::DELIMITER_OPTION, $options) ?
            $options[self::DELIMITER_OPTION] : $this->delimiter;
        $this->hasHeaderLine = array_key_exists(self::HAS_HEADER_OPTION, $options) ?
            $options[self::HAS_HEADER_OPTION] : $this->hasHeaderLine;
        $this->eolCharacter = array_key_exists(self::EOL_CHAR_OPTION, $options) ?
            $options[self::EOL_CHAR_OPTION] : $this->eolCharacter;
    }

    private function reset()
    {
        $this->delimiter = ',';
        $this->hasHeaderLine = false;
        $this->eolCharacter = "\n";
    }
}
