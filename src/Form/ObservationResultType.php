<?php

namespace App\Form;

use App\Entity\ObservationResult;
use App\Entity\PoliticalParty;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ObservationResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('party', EntityType::class, [
                'class' => PoliticalParty::class,
                'label' => 'Party',
                'choice_label' => 'name',
            ])
            ->add('observedVotes', IntegerType::class, [
                'label' => 'Observed votes',
                'constraints' => [new NotBlank(), new PositiveOrZero()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ObservationResult::class]);
    }
}