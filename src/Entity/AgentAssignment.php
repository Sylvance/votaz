<?php

namespace App\Entity;

use App\Entity\Enum\AssignmentStatus;
use App\Repository\AgentAssignmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgentAssignmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class AgentAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartyAgent::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PartyAgent $agent = null;

    #[ORM\ManyToOne(targetEntity: Election::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Election $election = null;

    #[ORM\ManyToOne(targetEntity: District::class)]
    private ?District $district = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pollingStation = null;

    #[ORM\Column(length: 150)]
    private ?string $assignedBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $assignedAt = null;

    #[ORM\Column(type: 'string', length: 16, enumType: AssignmentStatus::class)]
    private AssignmentStatus $status = AssignmentStatus::ACTIVE;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\OneToMany(mappedBy: 'assignment', targetEntity: ObservationReport::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $reports;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->reports = new ArrayCollection();
        $this->assignedAt = new \DateTimeImmutable();
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

    public function getAgent(): ?PartyAgent
    {
        return $this->agent;
    }

    public function setAgent(?PartyAgent $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getElection(): ?Election
    {
        return $this->election;
    }

    public function setElection(?Election $election): self
    {
        $this->election = $election;

        return $this;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): self
    {
        $this->district = $district;

        return $this;
    }

    public function getPollingStation(): ?string
    {
        return $this->pollingStation;
    }

    public function setPollingStation(?string $pollingStation): self
    {
        $this->pollingStation = $pollingStation;

        return $this;
    }

    public function getAssignedBy(): ?string
    {
        return $this->assignedBy;
    }

    public function setAssignedBy(string $assignedBy): self
    {
        $this->assignedBy = $assignedBy;

        return $this;
    }

    public function getAssignedAt(): ?\DateTimeImmutable
    {
        return $this->assignedAt;
    }

    public function setAssignedAt(\DateTimeImmutable $assignedAt): self
    {
        $this->assignedAt = $assignedAt;

        return $this;
    }

    public function getStatus(): AssignmentStatus
    {
        return $this->status;
    }

    public function setStatus(AssignmentStatus $status): self
    {
        $this->status = $status;

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
     * @return Collection<int, ObservationReport>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(ObservationReport $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
            $report->setAssignment($this);
        }

        return $this;
    }

    public function removeReport(ObservationReport $report): self
    {
        if ($this->reports->removeElement($report)) {
            if ($report->getAssignment() === $this) {
                $report->setAssignment(null);
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
}
