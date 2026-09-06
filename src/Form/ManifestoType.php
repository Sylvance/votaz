<?php

namespace App\Form;

use App\Entity\Manifesto;
use App\Entity\ManifestoSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManifestoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Manifesto title'])
            ->add('summary', TextareaType::class, [
                'label' => 'Executive summary',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Published — visible to the public',
                'required' => false,
            ])
            ->add('sections', CollectionType::class, [
                'entry_type' => ManifestoSectionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => 'Manifesto sections',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Manifesto::class]);
    }
}