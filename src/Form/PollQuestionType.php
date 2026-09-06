<?php

namespace App\Form;

use App\Entity\Enum\QuestionType;
use App\Entity\PollOption;
use App\Entity\PollQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PollQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Question'])
            ->add('type', ChoiceType::class, [
                'label' => 'Answer type',
                'choices' => [
                    'Single choice' => QuestionType::SINGLE_CHOICE,
                    'Multiple choice' => QuestionType::MULTIPLE_CHOICE,
                ],
            ])
            ->add('required', CheckboxType::class, ['label' => 'Required', 'required' => false])
            ->add('position', IntegerType::class, ['label' => 'Order'])
            ->add('options', CollectionType::class, [
                'entry_type' => PollOptionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Options',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PollQuestion::class]);
    }
}