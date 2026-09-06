<?php
namespace App\Service;
class GreetingGenerator
{
    public function getRandomGreeting(string $name): string
    {
        $greeting = ['Hey', 'Yo', 'Aloha'][random_int(0, 2)];
        return "$greeting $name!";
    }
}
