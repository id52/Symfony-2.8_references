<?php

namespace KreaLab\AdminBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class Cashbox extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $cashbox \KreaLab\CommonBundle\Entity\Cashbox */
        $cashbox = $options['data'];

        $builder
            ->add('active', CheckboxType::class, ['required' => false])
            ->add('legal_entity', EntityType::class, [
                'disabled'      => (bool)$cashbox->getId(),
                'class'         => 'CommonBundle:LegalEntity',
                'placeholder'   => 'choose_legal_entity',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('le')
                        ->andWhere('le.active = :active')->setParameter('active', true)
                        ->addOrderBy('le.name')
                    ;
                },
            ])
            ->add('num', TextType::class, [
                'disabled'    => (bool)$cashbox->getId(),
                'constraints' => new Assert\NotBlank(),
            ])
            ->add('inv_num', TextType::class, [
                'disabled'    => (bool)$cashbox->getId(),
                'constraints' => new Assert\NotBlank(),
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'cashbox';
    }
}
