<?php

namespace KreaLab\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class Eeg extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('active', CheckboxType::class, ['required' => false]);
        $builder->add('name', TextType::class, ['constraints' => new Assert\NotBlank()]);
        $builder->add('description', TextareaType::class, [
            'constraints' => new Assert\NotBlank(),
            'attr'        => ['class' => 'ckeditor'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'eeg';
    }
}
