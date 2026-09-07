<?php

namespace App\Form;

use App\Entity\Enum\PartyAgentStatus;
use App\Entity\PartyAgent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class PartyAgentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'First name'])
            ->add('lastName', TextType::class, ['label' => 'Last name'])
            ->add('email', EmailType::class, [
                'label' => 'Login email',
                'constraints' => [new NotBlank(), new Email()],
            ])
            ->add('phone', TextType::class, ['label' => 'Phone', 'required' => false])
            ->add('party', null, ['label' => 'Party'])
            ->add('agentCode', TextType::class, [
                'label' => 'Agent code (public ID shown to election officials)',
                'constraints' => [new NotBlank(), new Length(min: 4, max: 50), new Regex('/^[A-Za-z0-9-]+$/')],
            ])
            ->add('credentials', TextType::class, [
                'label' => 'Credentials / badge details',
                'required' => false,
            ])
            ->add('status', EnumType::class, [
                'class' => PartyAgentStatus::class,
                'label' => 'Status',
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event): void {
            $agent = $event->getData();
            $form = $event->getForm();

            if (!$agent || null === $agent->getId()) {
                $form->add('password', PasswordType::class, [
                    'label' => 'Initial password',
                    'required' => true,
                    'mapped' => false,
                    'constraints' => [new NotBlank(), new Length(min: 8, max: 4096)],
                ]);
            }

            $form->add('enabled', CheckboxType::class, ['label' => 'Account enabled can sign in', 'required' => false]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PartyAgent::class]);
    }
}
