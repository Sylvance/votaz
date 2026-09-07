<?php

namespace App\Form;

use App\Entity\Enum\PartyStatus;
use App\Entity\PoliticalParty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Party name'])
            ->add('abbreviation', TextType::class, ['label' => 'Abbreviation (e.g. MPC)'])
            ->add('registrationNumber', TextType::class, ['label' => 'Registration number'])
            ->add('leaderName', TextType::class, ['label' => 'Party leader name'])
            ->add('leaderTitle', TextType::class, [
                'label' => 'Leader title (e.g. National Chairperson)',
                'required' => false,
            ])
            ->add('leaderAnnouncedAt', DateType::class, [
                'label' => 'Leader announced on',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('motto', TextType::class, ['label' => 'Motto', 'required' => false])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('website', TextType::class, ['label' => 'Website', 'required' => false])
            ->add('email', EmailType::class, ['label' => 'Contact email', 'required' => false])
            ->add('phone', TextType::class, ['label' => 'Contact phone', 'required' => false])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Pending approval' => PartyStatus::PENDING,
                    'Approved' => PartyStatus::APPROVED,
                    'Rejected' => PartyStatus::REJECTED,
                    'Suspended' => PartyStatus::SUSPENDED,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PoliticalParty::class]);
    }
}
