<?php

namespace App\Entity;

use App\Entity\Enum\PartyAgentStatus;
use App\Repository\PartyAgentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: PartyAgentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PartyAgent implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\ManyToOne(targetEntity: PoliticalParty::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?PoliticalParty $party = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $agentCode = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $credentials = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_AGENT'];

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\Column(type: 'string', length: 16, enumType: PartyAgentStatus::class)]
    private PartyAgentStatus $status = PartyAgentStatus::ACTIVE;

    #[ORM\OneToMany(mappedBy: 'agent', targetEntity: AgentAssignment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $assignments;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return trim($this->firstName.' '.$this->lastName);
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
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

    public function getParty(): ?PoliticalParty
    {
        return $this->party;
    }

    public function setParty(?PoliticalParty $party): self
    {
        $this->party = $party;

        return $this;
    }

    public function getAgentCode(): ?string
    {
        return $this->agentCode;
    }

    public function setAgentCode(string $agentCode): self
    {
        $this->agentCode = $agentCode;

        return $this;
    }

    public function getCredentials(): ?string
    {
        return $this->credentials;
    }

    public function setCredentials(?string $credentials): self
    {
        $this->credentials = $credentials;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getStatus(): PartyAgentStatus
    {
        return $this->status;
    }

    public function setStatus(PartyAgentStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, AgentAssignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(AgentAssignment $assignment): self
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setAgent($this);
        }

        return $this;
    }

    public function removeAssignment(AgentAssignment $assignment): self
    {
        if ($this->assignments->removeElement($assignment)) {
            if ($assignment->getAgent() === $this) {
                $assignment->setAgent(null);
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_AGENT';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }
}
