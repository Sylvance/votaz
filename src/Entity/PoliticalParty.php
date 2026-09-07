<?php

namespace App\Entity;

use App\Entity\Enum\PartyStatus;
use App\Repository\PoliticalPartyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PoliticalPartyRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PoliticalParty
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $abbreviation = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motto = null;

    #[ORM\Column(length: 160)]
    private ?string $leaderName = null;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $leaderTitle = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $leaderAnnouncedAt = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $registrationNumber = null;

    #[ORM\Column(type: 'string', length: 16, enumType: PartyStatus::class)]
    private PartyStatus $status = PartyStatus::PENDING;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $registeredAt = null;

    #[ORM\OneToOne(targetEntity: Manifesto::class, mappedBy: 'party', cascade: ['persist', 'remove'])]
    private ?Manifesto $manifesto = null;

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

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(string $abbreviation): self
    {
        $this->abbreviation = $abbreviation;

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

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

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

    public function getLeaderName(): ?string
    {
        return $this->leaderName;
    }

    public function setLeaderName(string $leaderName): self
    {
        $this->leaderName = $leaderName;

        return $this;
    }

    public function getLeaderTitle(): ?string
    {
        return $this->leaderTitle;
    }

    public function setLeaderTitle(?string $leaderTitle): self
    {
        $this->leaderTitle = $leaderTitle;

        return $this;
    }

    public function getLeaderAnnouncedAt(): ?\DateTimeImmutable
    {
        return $this->leaderAnnouncedAt;
    }

    public function setLeaderAnnouncedAt(?\DateTimeImmutable $leaderAnnouncedAt): self
    {
        $this->leaderAnnouncedAt = $leaderAnnouncedAt;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(string $registrationNumber): self
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getStatus(): PartyStatus
    {
        return $this->status;
    }

    public function setStatus(PartyStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isApproved(): bool
    {
        return PartyStatus::APPROVED === $this->status;
    }

    public function getRegisteredAt(): ?\DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(?\DateTimeImmutable $registeredAt): self
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }

    public function getManifesto(): ?Manifesto
    {
        return $this->manifesto;
    }

    public function setManifesto(?Manifesto $manifesto): self
    {
        $this->manifesto = $manifesto;

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

    public function __toString(): string
    {
        return sprintf('%s (%s)', (string) $this->name, (string) $this->abbreviation);
    }
}
