<?php
namespace App\Service;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

class SupportChat
{
    public function __construct(
        private AgentInterface $agent,
    ) {
    }
    public function ask(string $question): string
    {
        $messages = new MessageBag(Message::ofUser($question));
        return $this->agent->call($messages)->getContent();
    }
}
