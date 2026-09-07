<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uniq_election_voter_vote', columns: ['election_id', 'voter_id'])]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Election::class, inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Election $election = null;

    #[ORM\ManyToOne(targetEntity: Candidate::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Candidate $candidate = null;

    #[ORM\ManyToOne(targetEntity: Voter::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Voter $voter = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $castAt = null;

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->castAt ??= new \DateTimeImmutable();
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

    public function getCandidate(): ?Candidate
    {
        return $this->candidate;
    }

    public function setCandidate(?Candidate $candidate): self
    {
        $this->candidate = $candidate;

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

    public function getCastAt(): ?\DateTimeImmutable
    {
        return $this->castAt;
    }

    public function setCastAt(?\DateTimeImmutable $castAt): self
    {
        $this->castAt = $castAt;

        return $this;
    }
}
