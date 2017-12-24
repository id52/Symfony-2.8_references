<?php

namespace KreaLab\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class Brigade extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, ['constraints' => new Assert\NotBlank()]);
        $builder->add('legal_entity', EntityType::class, [
            'class'       => 'CommonBundle:LegalEntity',
            'placeholder' => 'choose_legal_entity',
            'constraints' => new Assert\NotBlank(),
        ]);
    }

    public function getBlockPrefix()
    {
        return 'brigade';
    }
}
