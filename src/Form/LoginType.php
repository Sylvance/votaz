<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', TextType::class, [
                'label' => 'Email or voter number',
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('_password', PasswordType::class, [
                'label' => 'Password',
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('login', SubmitType::class, ['label' => 'Sign in']);
    }

    public function getBlockPrefix(): string
    {
        return 'login';
    }
}
