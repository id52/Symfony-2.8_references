<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractSettingsController;
use KreaLab\AdminSkeletonBundle\Form\Type\Measure;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SettingsController extends AbstractSettingsController
{
    protected $routerSettings = 'admin_settings';
    protected $tmplSettings   = 'AdminBundle::_settings.html.twig';

    protected function addSettingsFb(FormBuilderInterface $fb)
    {
        $fb->add('orderman_close_order_sum', Measure::class, [
            'attr'        => ['bsize' => 2],
            'measure'     => 'rub',
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\GreaterThan(0),
            ],
        ]);

        $fb->add('orderman_overrun', PercentType::class, [
            'attr'        => ['bsize' => 2],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\GreaterThan(0),
            ],
        ]);

        $fb->add('operator_blanks_on_hands_lost_title', TextType::class, [
            'constraints' => new Assert\NotBlank(),
        ]);
        $fb->add('operator_blanks_on_hands_lost_text', TextareaType::class, [
            'constraints' => new Assert\NotBlank(),
            'attr'        => ['class' => 'ckeditor'],
        ]);

        $fb->add('creating_referenceman_archive_box_text', TextareaType::class, [
            'constraints' => new Assert\NotBlank(),
            'attr'        => ['class' => 'ckeditor'],
        ]);

        $fb->add('current_referenceman_archive_box_text', TextareaType::class, [
            'constraints' => new Assert\NotBlank(),
            'attr'        => ['class' => 'ckeditor'],
        ]);

        $fb->add('creating_orderman_archive_box_text', TextareaType::class, [
            'constraints' => new Assert\NotBlank(),
            'attr'        => ['class' => 'ckeditor'],
        ]);

        $fb->add('current_orderman_archive_box_text', TextareaType::class, [
            'constraints' => new Assert\NotBlank(),
            'attr'        => ['class' => 'ckeditor'],
        ]);

        return $fb;
    }
}
