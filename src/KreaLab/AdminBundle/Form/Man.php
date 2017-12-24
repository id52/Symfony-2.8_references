<?php

namespace KreaLab\AdminBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class Man extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('last_name', TextType::class, ['constraints' => new Assert\NotBlank()]);
        $builder->add('first_name', TextType::class, ['constraints' => new Assert\NotBlank()]);
        $builder->add('patronymic', TextType::class, ['constraints' => new Assert\NotBlank()]);
        $builder->add('last_name_genitive', TextType::class, ['constraints' => new Assert\NotBlank()]);
        $builder->add('first_name_genitive', TextType::class, ['constraints' => new Assert\NotBlank()]);
        $builder->add('patronymic_genitive', TextType::class, ['constraints' => new Assert\NotBlank()]);

        $builder->add('brigade', EntityType::class, [
            'class'       => 'CommonBundle:Brigade',
            'placeholder' => 'choose_brigade',
            'constraints' => new Assert\NotBlank(),
        ]);

        $builder->add('specialty', EntityType::class, [
            'class'       => 'CommonBundle:Specialty',
            'placeholder' => 'choose_specialty',
            'constraints' => new Assert\NotBlank(),
        ]);
    }

    public function getBlockPrefix()
    {
        return 'man';
    }
}
