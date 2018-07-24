<?php declare(strict_types=1);

namespace ATS\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
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
                if ($typeGuess) {
                    if (!in_array(strtolower($field), ['password', 'salt', 'token'])) {
                        $builder->add($field, $typeGuess->getType(), $typeGuess->getOptions());
                    }
                }
            }
        }

        $builder->add('save', SubmitType::class);
        $builder->add('saveAndBackToList', SubmitType::class);
        $builder->add(
            'cancel',
            SubmitType::class,
            [
                'attr' => array(
                    'formnovalidate' => 'formnovalidate',
                ),
            ]
        );
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
        return 'AdminDocumentType';
    }


    private function isManaged(\ReflectionProperty $property)
    {
        if (preg_match("/@(ODM\\\\)?(Field|Reference|Embed)/", $property->getDocComment(), $matches)) {
            return true;
        }

        return false;
    }
}
