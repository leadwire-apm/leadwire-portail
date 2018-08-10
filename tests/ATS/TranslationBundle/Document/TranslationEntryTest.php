<?php

namespace Tests\ATS\TranslationBundle\Document;

use ATS\TranslationBundle\Document\TranslationEntry;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class TranslationEntryTest extends TestCase
{

    public function testGettersSetters()
    {
        $translationEntry = new TranslationEntry();
        $translationEntry->setKey('someKey')
            ->setValues([
                'fr' => 'Une Valeur',
                'en' => 'A Value',
            ]);

        $this->assertSame("someKey", $translationEntry->getKey());
        $this->assertSame([
            'fr' => 'Une Valeur',
            'en' => 'A Value',
        ], $translationEntry->getValues());
        $this->assertSame("Une Valeur", $translationEntry->getValueForLanguage('fr'));
        $this->assertSame("A Value", $translationEntry->getValueForLanguage('en'));
        $this->assertSame(null, $translationEntry->getValueForLanguage('de'));
        $this->assertSame(null, $translationEntry->getId());

        $translationEntry = new TranslationEntry('someKey', [
            'fr' => 'Une Valeur',
            'en' => 'A Value',
        ]);

        $this->assertSame("someKey", $translationEntry->getKey());
        $this->assertSame([
            'fr' => 'Une Valeur',
            'en' => 'A Value',
        ], $translationEntry->getValues());
        $this->assertSame("Une Valeur", $translationEntry->getValueForLanguage('fr'));
        $this->assertSame("A Value", $translationEntry->getValueForLanguage('en'));

    }
}
