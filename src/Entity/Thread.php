<?php

namespace App\Entity;

use App\Entity\Enum\ThreadCategory;
use App\Repository\ThreadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThreadRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Thread
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 16, enumType: ThreadCategory::class)]
    private ThreadCategory $category = ThreadCategory::GENERAL;

    #[ORM\Column(length: 200)]
    private ?string $title = null;

    #[ORM\Column(length: 230, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: Voter::class)]
    private ?Voter $author = null;

    #[ORM\Column(length: 200)]
    private ?string $authorName = null;

    #[ORM\ManyToOne(targetEntity: Election::class)]
    private ?Election $election = null;

    #[ORM\ManyToOne(targetEntity: PoliticalParty::class)]
    private ?PoliticalParty $party = null;

    #[ORM\ManyToOne(targetEntity: RoundTable::class)]
    private ?RoundTable $roundTable = null;

    #[ORM\Column]
    private bool $isPinned = false;

    #[ORM\Column]
    private bool $isLocked = false;

    #[ORM\OneToMany(mappedBy: 'thread', targetEntity: Post::class, cascade: ['remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $posts;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
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

    public function getCategory(): ThreadCategory
    {
        return $this->category;
    }

    public function setCategory(ThreadCategory $category): self
    {
        $this->category = $category;

        return $this;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?Voter
    {
        return $this->author;
    }

    public function setAuthor(?Voter $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;

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

    public function getParty(): ?PoliticalParty
    {
        return $this->party;
    }

    public function setParty(?PoliticalParty $party): self
    {
        $this->party = $party;

        return $this;
    }

    public function getRoundTable(): ?RoundTable
    {
        return $this->roundTable;
    }

    public function setRoundTable(?RoundTable $roundTable): self
    {
        $this->roundTable = $roundTable;

        return $this;
    }

    public function isPinned(): bool
    {
        return $this->isPinned;
    }

    public function setIsPinned(bool $isPinned): self
    {
        $this->isPinned = $isPinned;

        return $this;
    }

    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
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