<?php

namespace App\Entity;

use App\Entity\Enum\CandidateStatus;
use App\Repository\CandidateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Candidate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Election::class, inversedBy: 'candidates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Election $election = null;

    #[ORM\ManyToOne(targetEntity: PoliticalParty::class)]
    private ?PoliticalParty $party = null;

    #[ORM\ManyToOne(targetEntity: Voter::class)]
    private ?Voter $voter = null;

    #[ORM\Column(length: 200)]
    private ?string $fullName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motto = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $ballotPosition = 0;

    #[ORM\Column(type: 'string', length: 16, enumType: CandidateStatus::class)]
    private CandidateStatus $status = CandidateStatus::NOMINATED;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $declaredAt = null;

    #[ORM\ManyToOne(targetEntity: District::class)]
    private ?District $district = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->declaredAt ??= $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getParty(): ?PoliticalParty
    {
        return $this->party;
    }

    public function setParty(?PoliticalParty $party): self
    {
        $this->party = $party;

        return $this;
    }

    public function isIndependent(): bool
    {
        return null === $this->party;
    }

    public function getVoter(): ?Voter
    {
        return $this->voter;
    }

    public function setVoter(?Voter $voter): self
    {
        $this->voter = $voter;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    public function getMotto(): ?string
    {
        return $this->motto;
    }

    public function setMotto(?string $motto): self
    {
        $this->motto = $motto;

        return $this;
    }

    public function getBallotPosition(): int
    {
        return $this->ballotPosition;
    }

    public function setBallotPosition(int $ballotPosition): self
    {
        $this->ballotPosition = $ballotPosition;

        return $this;
    }

    public function getStatus(): CandidateStatus
    {
        return $this->status;
    }

    public function setStatus(CandidateStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDeclaredAt(): ?\DateTimeImmutable
    {
        return $this->declaredAt;
    }

    public function setDeclaredAt(?\DateTimeImmutable $declaredAt): self
    {
        $this->declaredAt = $declaredAt;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
