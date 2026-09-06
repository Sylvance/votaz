<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uniq_roundtable_voter', columns: ['round_table_id', 'voter_id'])]
class RoundTableRegistration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: RoundTable::class, inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RoundTable $roundTable = null;

    #[ORM\ManyToOne(targetEntity: Voter::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Voter $voter = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $registeredAt = null;

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->registeredAt ??= new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getVoter(): ?Voter
    {
        return $this->voter;
    }

    public function setVoter(?Voter $voter): self
    {
        $this->voter = $voter;

        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeImmutable
    {
        return $this->registeredAt;
    }
}