<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationConfirmationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Confirmation code',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex('/^\d{6}$/', message: 'The confirmation code is 6 digits.'),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Create a password',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 8]),
                    new Assert\Regex('/[A-Z]/', message: 'Include at least one uppercase letter.'),
                    new Assert\Regex('/[a-z]/', message: 'Include at least one lowercase letter.'),
                    new Assert\Regex('/\d/', message: 'Include at least one number.'),
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Repeat password',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => true,
        ]);
    }
}
