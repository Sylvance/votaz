<?php

namespace App\Form;

use App\Entity\Enum\QuestionType;
use App\Entity\Poll;
use App\Entity\PollOption;
use App\Entity\PollQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Title'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('isSurvey', CheckboxType::class, [
                'label' => 'Multi-question survey (tick to make this a survey instead of a single-question poll)',
                'required' => false,
            ])
            ->add('startsAt', DateTimeType::class, [
                'label' => 'Opens on',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => 'Closes on',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('requiresAuth', CheckboxType::class, [
                'label' => 'Only confirmed voters may respond',
                'required' => false,
            ])
            ->add('showResultsAfterEnd', CheckboxType::class, [
                'label' => 'Show results publicly after closing',
                'required' => false,
            ])
            ->add('questions', CollectionType::class, [
                'entry_type' => PollQuestionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Questions',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Poll::class]);
    }
}