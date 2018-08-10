<?php declare(strict_types=1);

namespace ATS\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmbeddedDocumentType extends AbstractType
{
    private $className;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $adminGuesser = $options['adminGuesser'];
        $this->className = $options['className'];
        $class = new \ReflectionClass($this->className);

        foreach ($class->getProperties() as $property) {
            if ($this->isManaged($property)) {
                $field = $property->name;
                $typeGuess = $adminGuesser->guessType($class->name, $field);
                $builder->add(
                    $field,
                    $typeGuess->getType(),
                    array_merge(
                        $typeGuess->getOptions(),
                        [
                            'attr' => [
                                'class' => 'form-control'
                            ]
                        ]
                    )
                );
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
         $resolver->setDefaults(
             array(
                 'className' => null,
                 'adminGuesser' => null,
             )
         );

         $resolver->setRequired('className');
         $resolver->setRequired('adminGuesser');
    }

    public function getName()
    {
        return 'AdminEmbeddedDocumentType';
    }

    private function isManaged(\ReflectionProperty $property)
    {
        if (preg_match("/@(ODM\\\\)?(Field|Reference|Embed)/", $property->getDocComment(), $matches)) {
            return true;
        }

        return false;
    }
}
