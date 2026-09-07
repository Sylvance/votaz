<?php

namespace App\Form;

use App\Entity\Enum\ThreadCategory;
use App\Entity\Thread;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThreadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Title'])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'choices' => [
                    'General discussion' => ThreadCategory::GENERAL,
                    'Election' => ThreadCategory::ELECTION,
                    'Party' => ThreadCategory::PARTY,
                    'Round table' => ThreadCategory::ROUND_TABLE,
                ],
            ])
            ->add('election', EntityType::class, [
                'class' => \App\Entity\Election::class,
                'choice_label' => 'name',
                'label' => 'Related election (optional)',
                'required' => false,
                'placeholder' => 'None',
            ])
            ->add('party', EntityType::class, [
                'class' => \App\Entity\PoliticalParty::class,
                'choice_label' => 'name',
                'label' => 'Related party (optional)',
                'required' => false,
                'placeholder' => 'None',
            ])
            ->add('content', TextareaType::class, ['label' => 'First post', 'attr' => ['rows' => 6]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Thread::class]);
    }
}
