<?php

namespace KreaLab\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ConsumableTagCategory extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('active', CheckboxType::class, ['required' => false]);
        $builder->add('name', TextType::class, ['constraints' => new Assert\NotBlank()]);
    }

    public function getBlockPrefix()
    {
        return 'consumable_tag_category';
    }
}
