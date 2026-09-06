<?php

namespace App\Form;

use App\Entity\Enum\RoundTableStatus;
use App\Entity\RoundTable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoundTableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Title'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'attr' => ['rows' => 5]])
            ->add('election', EntityType::class, [
                'class' => \App\Entity\Election::class,
                'choice_label' => 'name',
                'label' => 'Related election (optional)',
                'required' => false,
                'placeholder' => 'None',
            ])
            ->add('topic', TextType::class, ['label' => 'Discussion topic', 'required' => false])
            ->add('hostName', TextType::class, ['label' => 'Host / moderator', 'required' => false])
            ->add('scheduledAt', DateTimeType::class, [
                'label' => 'Scheduled for',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => 'Ends at',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('isOnline', CheckboxType::class, ['label' => 'Online event (video link instead of venue)', 'required' => false])
            ->add('location', TextType::class, ['label' => 'Venue or event link', 'required' => false])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Scheduled' => RoundTableStatus::SCHEDULED,
                    'Live now' => RoundTableStatus::LIVE,
                    'Completed' => RoundTableStatus::COMPLETED,
                    'Cancelled' => RoundTableStatus::CANCELLED,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => RoundTable::class]);
    }
}