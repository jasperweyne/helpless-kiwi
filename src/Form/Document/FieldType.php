<?php

namespace App\Form\Document;

<<<<<<< HEAD:src/Form/Document/FieldType.php
use App\Entity\Document\Field;
=======
use App\Entity\Person\PersonField;
use App\Form\Person\Dynamic\DynamicTypeRegistry;
>>>>>>> develop:src/Form/Person/PersonFieldType.php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldType extends AbstractType
{
    private $typeRegistry;

    public function __construct(DynamicTypeRegistry $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('slug', TextType::class, [
                'label' => 'Expressie naam',
                'required' => false,
            ])
            ->add('userEditOnly', CheckboxType::class, [
                'label' => 'Alleen aanpassen door gebruiker?',
                'required' => false,
            ])
            ->add('valueType', ChoiceType::class, [
                'choice_loader' => new CallbackChoiceLoader(function () {
<<<<<<< HEAD:src/Form/Document/FieldType.php
                    $vals = array_keys(DocumentType::TYPES());
=======
                    $vals = array_keys($this->typeRegistry->getTypes());
>>>>>>> develop:src/Form/Person/PersonFieldType.php

                    return array_combine($vals, $vals);
                }),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Field::class,
        ]);
    }
}
