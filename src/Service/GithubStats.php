<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitHubStats
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    public function getStars(array $repositories): array
    {
        // all these requests run concurrently, not one after another
        $responses = [];
        foreach ($repositories as $repository) {
            $responses[$repository] = $this->client->request(
                'GET', "https://api.github.com/repos/$repository"
            );
        }

        return array_map(
            fn ($response) => $response->toArray()['stargazers_count'],
            $responses,
        );
    }
}
