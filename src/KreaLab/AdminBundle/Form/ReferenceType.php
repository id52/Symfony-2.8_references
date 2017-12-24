<?php

namespace KreaLab\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ReferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('driver_reference', CheckboxType::class, [
            'required' => false,
            'disabled' => $options['blanks_cnt'],
        ]);
        $builder->add('is_serie', CheckboxType::class, [
            'required' => false,
            'disabled' => $options['blanks_cnt'],
        ]);
        $builder->add('name', TextType::class, ['constraints' => new Assert\NotBlank()]);
    }

    public function getBlockPrefix()
    {
        return 'reference_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'blanks_cnt' => null,
        ]);
    }
}
