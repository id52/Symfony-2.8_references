<?php

namespace KreaLab\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class Discount extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'constraints' => new Assert\NotBlank(),
        ]);

        $builder->add('is_online', CheckboxType::class, [
            'required' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'discount';
    }
}
