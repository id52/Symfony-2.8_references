<?php

namespace KreaLab\AdminBundle\Form;

use KreaLab\AdminSkeletonBundle\Form\Type\Measure;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class Service extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $entity \KreaLab\CommonBundle\Entity\Service */
        $entity = $options['data'];

        $builder
            ->add('active', CheckboxType::class, ['required' => false])
            ->add('is_eeg_conclusion')
            ->add('is_gnoch')
        ;

        if ($entity->getId()) {
            $builder->add('id', TextType::class, ['disabled' => true]);
        }

        $builder
            ->add('code', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('name', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('desc', TextareaType::class, ['constraints' => new Assert\NotBlank()])
            ->add('price', Measure::class, [
                'measure'     => 'rub',
                'attr'        => ['fsize' => 12],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThanOrEqual(0),
                ],
            ])
            ->add('is_not_revisit_price', CheckboxType::class, [
                'label'    => 'not_active',
                'required' => false,
            ])
            ->add('revisit_price', Measure::class, [
                'measure' => 'rub',
                'attr'    => ['fsize' => 12],
            ])
            ->add('is_not_duplicate_price', CheckboxType::class, [
                'label'    => 'not_active',
                'required' => false,
            ])
            ->add('duplicate_price', Measure::class, [
                'measure' => 'rub',
                'attr'    => ['fsize' => 12],
            ])
            ->add('lifetime', Measure::class, [
                'measure'     => 'month',
                'attr'        => ['fsize' => 2],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThanOrEqual(0),
                ],
            ])
            ->add('reference_type', EntityType::class, [
                'placeholder' => 'without_blank',
                'required'    => false,
                'class'       => 'CommonBundle:ReferenceType',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => new UniqueEntity(['fields' => 'code']),
        ]);
    }

    public function getBlockPrefix()
    {
        return 'service';
    }
}
