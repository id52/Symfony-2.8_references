<?php

namespace KreaLab\AdminBundle\Form;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class LegalEntity extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $legalEntity \KreaLab\CommonBundle\Entity\LegalEntity */
        $legalEntity = $options['data'];

        $builder->add('active', CheckboxType::class, ['required' => false]);
        if ($legalEntity->getId()) {
            $builder->add('id', TextType::class, ['disabled' => true]);
        }

        $builder
            ->add('name', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('short_name', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('license', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('address', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('checking_account', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('bank_name', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('correspondent_account', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('bik', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('inn', TextType::class, [
                'constraints' => new Assert\NotBlank(),
                'disabled'    => (bool)$legalEntity->getId(),
            ])
            ->add('ogrn', TextType::class, [
                'constraints' => new Assert\NotBlank(),
                'disabled'    => (bool)$legalEntity->getId(),
            ])
            ->add('kpp', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('person', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('person_genitive', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('phone', TextType::class, ['constraints' => new Assert\NotBlank()])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => new UniqueEntity(['fields' => 'short_name']),
        ]);
    }

    public function getBlockPrefix()
    {
        return 'legal_entity';
    }
}
