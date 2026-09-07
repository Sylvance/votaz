<?php

namespace App\Form;

use App\Entity\AgentAssignment;
use App\Entity\District;
use App\Entity\Election;
use App\Entity\PartyAgent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgentAssignmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('agent', EntityType::class, [
                'class' => PartyAgent::class,
                'label' => 'Party agent',
                'choice_label' => fn (PartyAgent $agent) => sprintf('%s (%s)', $agent->getFullName(), $agent->getParty()?->getAbbreviation() ?? 'no party'),
            ])
            ->add('election', EntityType::class, [
                'class' => Election::class,
                'label' => 'Election',
                'choice_label' => fn (Election $election) => sprintf('%s (%s — %s)', $election->getName(), $election->getVotingStartAt()?->format('M j, Y'), $election->getVotingEndAt()?->format('M j, Y')),
            ])
            ->add('district', EntityType::class, [
                'class' => District::class,
                'label' => 'District (optional)',
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'All districts / not specified',
            ])
            ->add('pollingStation', TextType::class, [
                'label' => 'Polling station (optional)',
                'required' => false,
            ])
            ->add('assignedAt', DateType::class, [
                'label' => 'Assigned on',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('notes', TextareaType::class, ['label' => 'Notes', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AgentAssignment::class]);
    }
}
