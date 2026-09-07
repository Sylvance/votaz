<?php

namespace App\Entity;

use App\Entity\Enum\ElectionStatus;
use App\Entity\Enum\ElectionType;
use App\Repository\ElectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElectionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Election
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $code = null;

    #[ORM\Column(type: 'string', length: 16, enumType: ElectionType::class)]
    private ElectionType $type = ElectionType::GENERAL;

    #[ORM\ManyToOne(targetEntity: District::class)]
    private ?District $district = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $registrationStartAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $registrationEndAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $nominationStartAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $nominationEndAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $votingStartAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $votingEndAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resultsPublishedAt = null;

    #[ORM\Column(type: 'string', length: 20, enumType: ElectionStatus::class)]
    private ElectionStatus $status = ElectionStatus::DRAFT;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $reason = null;

    #[ORM\OneToMany(mappedBy: 'election', targetEntity: Candidate::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $candidates;

    #[ORM\OneToMany(mappedBy: 'election', targetEntity: ElectionRegistration::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $registrations;

    #[ORM\OneToMany(mappedBy: 'election', targetEntity: Vote::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $votes;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->candidates = new ArrayCollection();
        $this->registrations = new ArrayCollection();
        $this->votes = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getType(): ElectionType
    {
        return $this->type;
    }

    public function setType(ElectionType $type): self
    {
        $this->type = $type;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getRegistrationStartAt(): ?\DateTimeImmutable
    {
        return $this->registrationStartAt;
    }

    public function setRegistrationStartAt(?\DateTimeImmutable $registrationStartAt): self
    {
        $this->registrationStartAt = $registrationStartAt;

        return $this;
    }

    public function getRegistrationEndAt(): ?\DateTimeImmutable
    {
        return $this->registrationEndAt;
    }

    public function setRegistrationEndAt(?\DateTimeImmutable $registrationEndAt): self
    {
        $this->registrationEndAt = $registrationEndAt;

        return $this;
    }

    public function getNominationStartAt(): ?\DateTimeImmutable
    {
        return $this->nominationStartAt;
    }

    public function setNominationStartAt(?\DateTimeImmutable $nominationStartAt): self
    {
        $this->nominationStartAt = $nominationStartAt;

        return $this;
    }

    public function getNominationEndAt(): ?\DateTimeImmutable
    {
        return $this->nominationEndAt;
    }

    public function setNominationEndAt(?\DateTimeImmutable $nominationEndAt): self
    {
        $this->nominationEndAt = $nominationEndAt;

        return $this;
    }

    public function getVotingStartAt(): ?\DateTimeImmutable
    {
        return $this->votingStartAt;
    }

    public function setVotingStartAt(\DateTimeImmutable $votingStartAt): self
    {
        $this->votingStartAt = $votingStartAt;

        return $this;
    }

    public function getVotingEndAt(): ?\DateTimeImmutable
    {
        return $this->votingEndAt;
    }

    public function setVotingEndAt(\DateTimeImmutable $votingEndAt): self
    {
        $this->votingEndAt = $votingEndAt;

        return $this;
    }

    public function getResultsPublishedAt(): ?\DateTimeImmutable
    {
        return $this->resultsPublishedAt;
    }

    public function setResultsPublishedAt(?\DateTimeImmutable $resultsPublishedAt): self
    {
        $this->resultsPublishedAt = $resultsPublishedAt;

        return $this;
    }

    public function getStatus(): ElectionStatus
    {
        return $this->status;
    }

    public function setStatus(ElectionStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function isVotingOpen(): bool
    {
        $now = new \DateTimeImmutable();

        return ElectionStatus::VOTING === $this->status
            && $now >= $this->votingStartAt
            && $now <= $this->votingEndAt;
    }

    public function isRegistrationOpen(): bool
    {
        $now = new \DateTimeImmutable();

        return ElectionStatus::REGISTRATION_OPEN === $this->status
            && $now >= ($this->registrationStartAt ?? new \DateTimeImmutable('@0'))
            && $now <= ($this->registrationEndAt ?? new \DateTimeImmutable('@9999999999'));
    }

    /**
     * @return Collection<int, Candidate>
     */
    public function getCandidates(): Collection
    {
        return $this->candidates;
    }

    /**
     * @return Collection<int, Candidate>
     */
    public function getApprovedCandidates(): array
    {
        return array_values(
            array_filter(
                $this->candidates->toArray(),
                static fn (Candidate $candidate): bool => Enum\CandidateStatus::APPROVED === $candidate->getStatus(),
            )
        );
    }

    /**
     * @return Collection<int, ElectionRegistration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
