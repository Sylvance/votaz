<?php

namespace App\Form;

use App\Entity\Election;
use App\Entity\Enum\ElectionStatus;
use App\Entity\Enum\ElectionType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ElectionTypeForm extends AbstractType
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Election name',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(['max' => 200])],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Election type',
                'choices' => [
                    'General election' => ElectionType::GENERAL,
                    'By-election' => ElectionType::BY_ELECTION,
                    'Partial election' => ElectionType::PARTIAL,
                    'Referendum' => ElectionType::REFERENDUM,
                ],
            ])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('registrationStartAt', DateTimeType::class, [
                'label' => 'Registration starts',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('registrationEndAt', DateTimeType::class, [
                'label' => 'Registration ends',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('nominationStartAt', DateTimeType::class, [
                'label' => 'Nominations open',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('nominationEndAt', DateTimeType::class, [
                'label' => 'Nominations close',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('votingStartAt', DateTimeType::class, [
                'label' => 'Voting starts',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new Assert\NotNull()],
            ])
            ->add('votingEndAt', DateTimeType::class, [
                'label' => 'Voting ends',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new Assert\NotNull()],
            ])
            ->add('district', EntityType::class, [
                'class' => \App\Entity\District::class,
                'choice_label' => 'name',
                'placeholder' => 'Nationwide (all districts)',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Draft' => ElectionStatus::DRAFT,
                    'Registration open' => ElectionStatus::REGISTRATION_OPEN,
                    'Nominations' => ElectionStatus::NOMINATION,
                    'Voting in progress' => ElectionStatus::VOTING,
                    'Results published' => ElectionStatus::RESULTS_PUBLISHED,
                    'Cancelled' => ElectionStatus::CANCELLED,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Election::class,
        ]);
    }
}