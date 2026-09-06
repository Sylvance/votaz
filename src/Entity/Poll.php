<?php

namespace App\Entity;

use App\Repository\PollRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PollRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Poll
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $isSurvey = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column]
    private bool $requiresAuth = true;

    #[ORM\Column]
    private bool $showResultsAfterEnd = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\OneToMany(mappedBy: 'poll', targetEntity: PollQuestion::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $questions;

    #[ORM\OneToMany(mappedBy: 'poll', targetEntity: PollResponse::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $responses;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->responses = new ArrayCollection();
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

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isSurvey(): bool
    {
        return $this->isSurvey;
    }

    public function setIsSurvey(bool $isSurvey): self
    {
        $this->isSurvey = $isSurvey;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(?\DateTimeImmutable $startsAt): self
    {
        $this->startsAt = $startsAt;

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

    public function requiresAuth(): bool
    {
        return $this->requiresAuth;
    }

    public function setRequiresAuth(bool $requiresAuth): self
    {
        $this->requiresAuth = $requiresAuth;

        return $this;
    }

    public function getShowResultsAfterEnd(): bool
    {
        return $this->showResultsAfterEnd;
    }

    public function setShowResultsAfterEnd(bool $showResultsAfterEnd): self
    {
        $this->showResultsAfterEnd = $showResultsAfterEnd;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * @return Collection<int, PollQuestion>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(PollQuestion $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setPoll($this);
        }

        return $this;
    }

    public function removeQuestion(PollQuestion $question): self
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getPoll() === $this) {
                $question->setPoll(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PollResponse>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isOpen(): bool
    {
        $now = new \DateTimeImmutable();

        if (null !== $this->startsAt && $now < $this->startsAt) {
            return false;
        }

        if (null !== $this->endsAt && $now > $this->endsAt) {
            return false;
        }

        return true;
    }

    public function resultsVisible(): bool
    {
        if ($this->showResultsAfterEnd) {
            if (null === $this->endsAt || new \DateTimeImmutable() > $this->endsAt) {
                return true;
            }
        }

        return $this->publishedAt !== null && new \DateTimeImmutable() > $this->publishedAt;
    }
}