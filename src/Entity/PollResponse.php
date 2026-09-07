<?php

namespace App\Entity;

use App\Repository\PollResponseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PollResponseRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uniq_poll_voter', columns: ['poll_id', 'voter_id'])]
class PollResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Poll::class, inversedBy: 'responses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Poll $poll = null;

    #[ORM\ManyToOne(targetEntity: Voter::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Voter $voter = null;

    #[ORM\OneToMany(mappedBy: 'response', targetEntity: PollSelection::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $selections;

    #[ORM\Column]
    private ?\DateTimeImmutable $submittedAt = null;

    public function __construct()
    {
        $this->selections = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->submittedAt ??= new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(?Poll $poll): self
    {
        $this->poll = $poll;

        return $this;
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

    /**
     * @return Collection<int, PollSelection>
     */
    public function getSelections(): Collection
    {
        return $this->selections;
    }

    public function addSelection(PollSelection $selection): self
    {
        if (!$this->selections->contains($selection)) {
            $this->selections->add($selection);
            $selection->setResponse($this);
        }

        return $this;
    }

    public function removeSelection(PollSelection $selection): self
    {
        if ($this->selections->removeElement($selection)) {
            if ($selection->getResponse() === $this) {
                $selection->setResponse(null);
            }
        }

        return $this;
    }

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }
}
