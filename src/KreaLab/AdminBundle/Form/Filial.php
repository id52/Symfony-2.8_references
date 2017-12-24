<?php

namespace KreaLab\AdminBundle\Form;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class Filial extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $filial \KreaLab\CommonBundle\Entity\Filial */
        $filial = $options['data'];

        $builder->add('active', CheckboxType::class, ['required' => false]);
        if ($filial->getId()) {
            $builder->add('id', TextType::class, ['disabled' => true]);
        }

        $builder->add('name', TextType::class, ['constraints' => new Assert\NotBlank()]);
        $builder->add('name_short', TextType::class, ['constraints' => new Assert\NotBlank()]);
        $builder->add('address', TextType::class, ['constraints' => new Assert\NotBlank()]);
        $builder->add('ips', CollectionType::class, [
            'constraints'    => new Assert\NotBlank(),
            'error_bubbling' => false,
            'required'       => true,
            'allow_add'      => true,
            'allow_delete'   => true,
            'delete_empty'   => true,
            'attr'           => [
                'bsize' => 4,
                'class' => 'collection_field',
            ],
            'entry_options'  => [
                'label'       => false,
                'required'    => false,
                'constraints' => new Assert\Ip(),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => new UniqueEntity(['fields' => 'name_short']),
        ]);
    }

    public function getBlockPrefix()
    {
        return 'filial';
    }
}
