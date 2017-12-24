<?php

namespace KreaLab\AdminBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class Workplace extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $workplace \KreaLab\CommonBundle\Entity\Workplace */
        $workplace = $options['data'];

        $builder->add('active', CheckboxType::class, ['required' => false]);
        if ($workplace->getId()) {
            $builder->add('id', TextType::class, ['disabled' => true]);
        }

        $builder
            ->add('name', TextType::class, ['constraints' => new Assert\NotBlank()])
            ->add('filial', EntityType::class, [
                'disabled'      => (bool)$workplace->getId(),
                'class'         => 'CommonBundle:Filial',
                'placeholder'   => 'choose_filial',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('f')
                        ->andWhere('f.active = :active')->setParameter('active', true)
                        ->addOrderBy('f.name')
                    ;
                },
            ])
            ->add('legal_entity', EntityType::class, [
                'class'         => 'CommonBundle:LegalEntity',
                'placeholder'   => 'choose_legal_entity',
                'query_builder' => function (EntityRepository $er) use ($workplace) {
                    return $er->createQueryBuilder('le')
                        ->andWhere('le.active = :active')->setParameter('active', true)
                        ->addOrderBy('le.name')
                    ;
                },
            ])
            ->add('cashboxes', EntityType::class, [
                'multiple'      => true,
                'expanded'      => true,
                'required'      => false,
                'class'         => 'CommonBundle:Cashbox',
                'query_builder' => function (EntityRepository $er) use ($workplace) {
                    $qb = $er->createQueryBuilder('c')
                        ->andWhere('c.active = :active')->setParameter('active', true)
                        ->leftJoin('c.legal_entity', 'le')
                        ->addOrderBy('le.name')
                        ->addOrderBy('c.num')
                    ;
                    if ($workplace->getId()) {
                        $qb
                            ->andWhere('c.workplace IS NULL OR c.workplace = :workplace')
                            ->setParameter('workplace', $workplace)
                        ;
                    } else {
                        $qb->andWhere('c.workplace IS NULL');
                    }

                    return $qb;
                },
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'workplace';
    }
}
