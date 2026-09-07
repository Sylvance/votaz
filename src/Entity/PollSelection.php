<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'uniq_response_question_option', columns: ['response_id', 'question_id', 'option_id'])]
class PollSelection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PollResponse::class, inversedBy: 'selections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PollResponse $response = null;

    #[ORM\ManyToOne(targetEntity: PollQuestion::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?PollQuestion $question = null;

    #[ORM\ManyToOne(targetEntity: PollOption::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?PollOption $option = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResponse(): ?PollResponse
    {
        return $this->response;
    }

    public function setResponse(?PollResponse $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getQuestion(): ?PollQuestion
    {
        return $this->question;
    }

    public function setQuestion(?PollQuestion $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getOption(): ?PollOption
    {
        return $this->option;
    }

    public function setOption(?PollOption $option): self
    {
        $this->option = $option;

        return $this;
    }
}
