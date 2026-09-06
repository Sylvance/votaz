<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class VoterSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('term', TextType::class, [
                'label' => 'Search voters',
                'required' => false,
                'attr' => ['placeholder' => 'Name, national ID, voter number, confirmation code'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => false,
                'choices' => [
                    'Pending' => 'pending',
                    'Confirmed' => 'confirmed',
                    'Rejected' => 'rejected',
                    'Deactivated' => 'deactivated',
                ],
                'placeholder' => 'All statuses',
            ])
            ->add('search', SubmitType::class, ['label' => 'Search']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => false,
            'method' => 'GET',
        ]);
    }
}