<?php declare(strict_types=1);

namespace ATS\TranslationBundle\Service;

class TranslationKeysExtractor
{
    const TRANSLATION_REGEX = "/trans(late)?\(\'(?P<key>[a-zA-Z\.]+)\'\)/";

    public function __construct()
    {
    }

    public function extract($filePath)
    {
        $translationKeys = [];
        $content = explode("\n", file_get_contents($filePath));
        foreach ($content as $line) {
            $matches = [];
            preg_match(self::TRANSLATION_REGEX, $line, $matches);
            if (count($matches) && array_key_exists('key', $matches)) {
                $translationKeys[] = $matches['key'];
            }
        }

        return $translationKeys;
    }
}
