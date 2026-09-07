<?php

namespace App\Entity;

use App\Repository\ObservationResultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ObservationResultRepository::class)]
class ObservationResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ObservationReport::class, inversedBy: 'results')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ObservationReport $report = null;

    #[ORM\ManyToOne(targetEntity: PoliticalParty::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?PoliticalParty $party = null;

    #[ORM\Column]
    private ?int $observedVotes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReport(): ?ObservationReport
    {
        return $this->report;
    }

    public function setReport(?ObservationReport $report): self
    {
        $this->report = $report;

        return $this;
    }

    public function getParty(): ?PoliticalParty
    {
        return $this->party;
    }

    public function setParty(?PoliticalParty $party): self
    {
        $this->party = $party;

        return $this;
    }

    public function getObservedVotes(): ?int
    {
        return $this->observedVotes;
    }

    public function setObservedVotes(?int $observedVotes): self
    {
        $this->observedVotes = $observedVotes;

        return $this;
    }
}
