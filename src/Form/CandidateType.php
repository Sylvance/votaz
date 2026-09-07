<?php

namespace App\Form;

use App\Entity\Candidate;
use App\Entity\Enum\CandidateStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, ['label' => 'Candidate full name'])
            ->add('party', EntityType::class, [
                'class' => \App\Entity\PoliticalParty::class,
                'choice_label' => 'name',
                'label' => 'Political party',
                'placeholder' => 'Independent',
                'required' => false,
            ])
            ->add('district', EntityType::class, [
                'class' => \App\Entity\District::class,
                'choice_label' => 'name',
                'label' => 'District / constituency',
                'required' => false,
                'placeholder' => 'Nationwide',
            ])
            ->add('motto', TextType::class, ['label' => 'Campaign motto', 'required' => false])
            ->add('bio', TextareaType::class, ['label' => 'Biography', 'required' => false, 'attr' => ['rows' => 5]])
            ->add('ballotPosition', IntegerType::class, ['label' => 'Ballot position'])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Nominated' => CandidateStatus::NOMINATED,
                    'Approved' => CandidateStatus::APPROVED,
                    'Rejected' => CandidateStatus::REJECTED,
                    'Withdrawn' => CandidateStatus::WITHDRAWN,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Candidate::class]);
    }
}
