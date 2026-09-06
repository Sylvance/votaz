<?php

namespace App\Entity;

use App\Entity\Enum\RoundTableStatus;
use App\Repository\RoundTableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoundTableRepository::class)]
#[ORM\HasLifecycleCallbacks]
class RoundTable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Election::class)]
    private ?Election $election = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $topic = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $scheduledAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column]
    private bool $isOnline = false;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $hostName = null;

    #[ORM\Column(type: 'string', length: 16, enumType: RoundTableStatus::class)]
    private RoundTableStatus $status = RoundTableStatus::SCHEDULED;

    #[ORM\OneToMany(mappedBy: 'roundTable', targetEntity: RoundTableRegistration::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $registrations;

    #[ORM\OneToMany(mappedBy: 'roundTable', targetEntity: Thread::class)]
    private Collection $threads;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
        $this->threads = new ArrayCollection();
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getElection(): ?Election
    {
        return $this->election;
    }

    public function setElection(?Election $election): self
    {
        $this->election = $election;

        return $this;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(?string $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    public function getScheduledAt(): ?\DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(\DateTimeImmutable $scheduledAt): self
    {
        $this->scheduledAt = $scheduledAt;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeImmutable $endsAt): self
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function isOnline(): bool
    {
        return $this->isOnline;
    }

    public function setIsOnline(bool $isOnline): self
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    public function getHostName(): ?string
    {
        return $this->hostName;
    }

    public function setHostName(?string $hostName): self
    {
        $this->hostName = $hostName;

        return $this;
    }

    public function getStatus(): RoundTableStatus
    {
        return $this->status;
    }

    public function setStatus(RoundTableStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, RoundTableRegistration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    /**
     * @return Collection<int, Thread>
     */
    public function getThreads(): Collection
    {
        return $this->threads;
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