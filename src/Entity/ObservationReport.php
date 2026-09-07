<?php

namespace App\Entity;

use App\Repository\ObservationReportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ObservationReportRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ObservationReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AgentAssignment::class, inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AgentAssignment $assignment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $submittedAt = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column]
    private bool $votingStartObserved = false;

    #[ORM\Column]
    private bool $votingEndObserved = false;

    #[ORM\Column]
    private bool $irregularities = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $irregularityDetails = null;

    #[ORM\Column(nullable: true)]
    private ?int $estimatedTurnout = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ObservationPhoto::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $photos;

    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ObservationResult::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $results;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
        $this->results = new ArrayCollection();
        $this->submittedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAssignment(): ?AgentAssignment
    {
        return $this->assignment;
    }

    public function setAssignment(?AgentAssignment $assignment): self
    {
        $this->assignment = $assignment;

        return $this;
    }

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(\DateTimeImmutable $submittedAt): self
    {
        $this->submittedAt = $submittedAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isVotingStartObserved(): bool
    {
        return $this->votingStartObserved;
    }

    public function setVotingStartObserved(bool $votingStartObserved): self
    {
        $this->votingStartObserved = $votingStartObserved;

        return $this;
    }

    public function isVotingEndObserved(): bool
    {
        return $this->votingEndObserved;
    }

    public function setVotingEndObserved(bool $votingEndObserved): self
    {
        $this->votingEndObserved = $votingEndObserved;

        return $this;
    }

    public function isIrregularities(): bool
    {
        return $this->irregularities;
    }

    public function setIrregularities(bool $irregularities): self
    {
        $this->irregularities = $irregularities;

        return $this;
    }

    public function getIrregularityDetails(): ?string
    {
        return $this->irregularityDetails;
    }

    public function setIrregularityDetails(?string $irregularityDetails): self
    {
        $this->irregularityDetails = $irregularityDetails;

        return $this;
    }

    public function getEstimatedTurnout(): ?int
    {
        return $this->estimatedTurnout;
    }

    public function setEstimatedTurnout(?int $estimatedTurnout): self
    {
        $this->estimatedTurnout = $estimatedTurnout;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Collection<int, ObservationPhoto>
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(ObservationPhoto $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setReport($this);
        }

        return $this;
    }

    public function removePhoto(ObservationPhoto $photo): self
    {
        if ($this->photos->removeElement($photo)) {
            if ($photo->getReport() === $this) {
                $photo->setReport(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, ObservationResult>
     */
    public function getResults(): Collection
    {
        return $this->results;
    }

    public function addResult(ObservationResult $result): self
    {
        if (!$this->results->contains($result)) {
            $this->results->add($result);
            $result->setReport($this);
        }

        return $this;
    }

    public function removeResult(ObservationResult $result): self
    {
        if ($this->results->removeElement($result)) {
            if ($result->getReport() === $this) {
                $result->setReport(null);
            }
        }

        return $this;
    }
}
