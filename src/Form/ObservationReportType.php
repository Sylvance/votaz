<?php

namespace App\Form;

use App\Entity\ObservationReport;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class ObservationReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, ['label' => 'What happened at the poll?'])
            ->add('votingStartObserved', CheckboxType::class, [
                'label' => 'Opening was observed and orderly',
                'required' => false,
            ])
            ->add('votingEndObserved', CheckboxType::class, [
                'label' => 'Close of poll was observed and orderly',
                'required' => false,
            ])
            ->add('irregularities', CheckboxType::class, [
                'label' => 'Irregularities were observed',
                'required' => false,
            ])
            ->add('irregularityDetails', TextareaType::class, [
                'label' => 'Irregularity details',
                'required' => false,
            ])
            ->add('estimatedTurnout', IntegerType::class, [
                'label' => 'Estimated voters seen at the poll',
                'required' => false,
            ])
            ->add('notes', TextareaType::class, ['label' => 'Extra notes', 'required' => false])
            ->add('photos', FileType::class, [
                'label' => 'Photo evidence (up to 5 images)',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new All([
                        new File(
                            maxSize: '8M',
                            mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/heic'],
                        ),
                    ]),
                ],
            ])
            ->add('results', CollectionType::class, [
                'label' => 'Observed party tallies (results you witnessed counted)',
                'entry_type' => ObservationResultType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ObservationReport::class]);
    }
}