<?php

namespace KreaLab\AdminBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class User extends AbstractType
{
    /** @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoder */
    protected $encoder;

    /** @var \KreaLab\CommonBundle\Entity\User */
    protected $user;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->encoder = $options['encoder'];
        $this->user    = $options['user'];

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var $entity \KreaLab\CommonBundle\Entity\User */
            $entity = $event->getData();
            $form   = $event->getForm();

            $form->add('active', CheckboxType::class, ['required' => false]);
            if ($entity->getId()) {
                $form->add('id', TextType::class, ['disabled' => true]);
            }

            $form->add('username', TextType::class, [
                'disabled'    => $entity->getId(),
                'constraints' => new Assert\NotBlank(),
            ]);
            $options = ['mapped' => false];
            if ($entity && $entity->getId()) {
                $options['required'] = false;
            } else {
                $options['constraints'] = new Assert\NotBlank();
            }

            $form->add('password_plain', TextType::class, $options);
            $roles = [];
            if ($this->user->hasRole('ROLE_SUPERADMIN')) {
                $roles = [
                    'ROLE_SUPERADMIN'  => 'ROLE_SUPERADMIN',
                    'ROLE_ADMIN'       => 'ROLE_ADMIN',
                    'ROLE_ARCHIVARIUS' => 'ROLE_ARCHIVARIUS',
                ];
            }

            if ($this->user->hasOneOfRoles(['ROLE_SUPERADMIN', 'ROLE_ADMIN'])) {
                $roles = array_merge($roles, [
                    'ROLE_MANAGE_FILIALS' => 'ROLE_MANAGE_FILIALS',
                    'ROLE_MANAGE_WORKERS' => 'ROLE_MANAGE_WORKERS',
                    'ROLE_CASHIER'        => 'ROLE_CASHIER',
                    'ROLE_SUPERVISOR'     => 'ROLE_SUPERVISOR',
                    'ROLE_TREASURER'      => 'ROLE_TREASURER',
                    'ROLE_ORDERMAN'       => 'ROLE_ORDERMAN',
                    'ROLE_ACQUITTANCEMAN' => 'ROLE_ACQUITTANCEMAN',
                    'ROLE_STOCKMAN'       => 'ROLE_STOCKMAN',
                    'ROLE_REFERENCEMAN'   => 'ROLE_REFERENCEMAN',
                    'ROLE_REPLACER'       => 'ROLE_REPLACER',
                ]);
            }

            $roles = array_merge($roles, [
                'ROLE_COURIER'         => 'ROLE_COURIER',
                'ROLE_SENIOR_OPERATOR' => 'ROLE_SENIOR_OPERATOR',
                'ROLE_OPERATOR'        => 'ROLE_OPERATOR',
            ]);
            $form
                ->add('last_name', TextType::class, ['constraints' => new Assert\NotBlank()])
                ->add('first_name', TextType::class, ['constraints' => new Assert\NotBlank()])
                ->add('patronymic', TextType::class, ['constraints' => new Assert\NotBlank()])
                ->add('roles', ChoiceType::class, [
                    'required'          => false,
                    'multiple'          => true,
                    'expanded'          => true,
                    'choices_as_values' => true,
                    'choices'           => $roles,
                ])
                ->add('phone', TextType::class, [
                    'required' => false,
                    'attr'     => ['bsize' => 3],
                ])
                ->add('ips', CollectionType::class, [
                    'required'      => false,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'delete_empty'  => true,
                    'attr'          => [
                        'bsize' => 4,
                        'class' => 'collection_field',
                    ],
                    'entry_options' => [
                        'label'       => false,
                        'required'    => false,
                        'constraints' => new Assert\Ip(),
                    ],
                ])
                ->add('power_attorney', TextType::class, [
                    'required' => false,
                ])
                ->add('filials', EntityType::class, [
                    'required'      => false,
                    'class'         => 'CommonBundle:Filial',
                    'multiple'      => true,
                    'expanded'      => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('f')
                            ->andWhere('f.active = :active')->setParameter('active', true)
                            ->addOrderBy('f.name')
                        ;
                    },
                ])
                ->add('filial', EntityType::class, [
                    'required'      => false,
                    'mapped'        => false,
                    'class'         => 'CommonBundle:Filial',
                    'placeholder'   => 'choose_filial',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('f')
                            ->andWhere('f.active = :active')->setParameter('active', true)
                            ->addOrderBy('f.name')
                        ;
                    },
                    'data'          => $entity->getWorkplace() ? $entity->getWorkplace()->getFilial() : null,
                ])
                ->add('workplace', EntityType::class, [
                    'required'      => false,
                    'class'         => 'CommonBundle:Workplace',
                    'choice_label'  => 'nameWithLegalEntity',
                    'placeholder'   => 'choose_workplace',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('w')
                            ->andWhere('w.active = :active')->setParameter('active', true)
                            ->leftJoin('w.filial', 'f')
                            ->andWhere('f.active = :factive')->setParameter('factive', true)
                            ->addOrderBy('f.name')
                            ->addOrderBy('w.name')
                        ;
                    },
                ])
            ;
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var $entity \KreaLab\CommonBundle\Entity\User */
            $entity = $event->getData();
            $form   = $event->getForm();

            $password = $form->get('password_plain')->getData();
            if ($password) {
                $entity->setPassword($this->encoder->encodePassword($entity, $password));
                if (!$entity->hasOneOfRoles(['ROLE_SUPERADMIN', 'ROLE_ADMIN'])) {
                    $entity->setForceChangePass(true);
                }
            }

            $phone = $form->get('phone')->getData();
            if ($phone != '' && $phone != '+7 (___) ___-__-__') {
                if (preg_match('#^\+7 \((\d{3})\) (\d{3})\-(\d{2})\-(\d{2})$#misu', $phone, $matches)) {
                    $entity->setPhone($matches[1].$matches[2].$matches[3].$matches[4]);
                } else {
                    $form->get('phone')->addError(new FormError('Неверный формат номера телефона'));
                }
            } else {
                $entity->setPhone(null);
            }

            if ($entity->isOperator()) {
                if (!$form->get('power_attorney')->getData()) {
                    $form->get('power_attorney')->addError(new FormError('Необходимо указать доверенность'));
                }

                $entity->setPhone(null);
                $entity->setIps([]);
                $filials = $entity->getFilials();
                foreach ($filials as $filial) { /** @var $filial \KreaLab\CommonBundle\Entity\Filial */
                    $entity->removeFilial($filial);
                }
            } else {
                $entity->setWorkplace(null);
                if (!$entity->hasOneOfRoles(['ROLE_SUPERADMIN', 'ROLE_ADMIN', 'ROLE_ACQUITTANCEMAN'])) {
                    $entity->setPhone(null);
                }

                if (!$entity->hasOneOfRoles([
                    'ROLE_SUPERADMIN',
                    'ROLE_ADMIN',
                    'ROLE_ARCHIVARIUS',
                    'ROLE_MANAGE_FILIALS',
                    'ROLE_MANAGE_WORKERS',
                    'ROLE_CASHIER',
                    'ROLE_SUPERVISOR',
                ])
                ) {
                    $entity->setIps([]);
                }

                if (!$entity->hasOneOfRoles(['ROLE_SENIOR_OPERATOR', 'ROLE_COURIER'])) {
                    $filials = $entity->getFilials();
                    foreach ($filials as $filial) { /** @var $filial \KreaLab\CommonBundle\Entity\Filial */
                        $entity->removeFilial($filial);
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => new UniqueEntity(['fields' => 'username']),
            'encoder'     => null,
            'user'        => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'user';
    }
}
