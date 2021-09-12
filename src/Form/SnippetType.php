<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Language;
use App\Entity\Snippet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SnippetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Snippet name',
            ])
            ->add('language', EntityType::class, [
                'label' => 'Language',
                'class' => Language::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose language...',
            ])
            ->add('code', TextareaType::class, [
                'label' => 'Code',
                'attr' => [
                    'rows' => '20',
                    'style' => 'font-family: monospace;',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Snippet::class,
        ]);
    }
}
