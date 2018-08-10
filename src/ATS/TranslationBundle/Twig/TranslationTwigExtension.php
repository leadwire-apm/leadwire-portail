<?php declare(strict_types=1);

namespace ATS\TranslationBundle\Twig;

use ATS\TranslationBundle\Manager\TranslationEntryManager;
use Symfony\Component\Translation\Translator;

/**
 * @deprecated
 */
class TranslationTwigExtension extends \Twig_Extension
{
    /**
     * translationEntryManager
     *
     * @var TranslationEntryManager
     */
    private $translationManager;

    public function __construct(TranslationEntryManager $manager)
    {
        $this->translationManager = $manager;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('translate', array($this, 'translate')),
        );
    }

    public function translate($id, $locale = null, array $parameters = array())
    {
        $message = $this->translationManager->getByKeyAndLanguage($id, $locale);

        return strtr($message, $parameters);
    }
}
