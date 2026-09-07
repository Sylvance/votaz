<?php

namespace App\Form;

use App\Entity\Voter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class VoterRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nationalId', TextType::class, [
                'label' => 'National ID number',
                'attr' => ['autocomplete' => 'off'],
            ])
            ->add('firstName', TextType::class, ['label' => 'First name'])
            ->add('middleName', TextType::class, ['label' => 'Middle name', 'required' => false])
            ->add('lastName', TextType::class, ['label' => 'Last name'])
            ->add('gender', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => 'Gender',
                'choices' => [
                    'Male' => 'M',
                    'Female' => 'F',
                    'Other' => 'O',
                ],
                'expanded' => true,
            ])
            ->add('dateOfBirth', DateType::class, [
                'label' => 'Date of birth',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [
                    new Assert\NotNull(),
                    new Assert\LessThanOrEqual('today', message: 'Date of birth cannot be in the future.'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email address',
                'required' => false,
            ])
            ->add('phone', TextType::class, [
                'label' => 'Phone number',
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'label' => 'Residential address',
                'required' => false,
            ])
            ->add('city', TextType::class, ['label' => 'City', 'required' => false])
            ->add('region', TextType::class, ['label' => 'Region/State', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Voter::class,
            'validation_groups' => ['registration'],
        ]);
    }
}
