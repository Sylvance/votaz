<?php
namespace App\Controller;

use App\Service\GreetingGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GreetingController
{
    public function __construct(
        private GreetingGenerator $greetingGenerator,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/greet/{name}')]
    public function greet(string $name): Response
    {
        $this->logger->info("Greeting $name");
        $greeting = $this->greetingGenerator->getRandomGreeting($name);
        return new Response($greeting);
    }
}
