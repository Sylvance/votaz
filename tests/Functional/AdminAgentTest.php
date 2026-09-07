<?php

namespace App\Tests\Functional;

class AdminAgentTest extends BaseWebTestCase
{
    public function testAdminCanManageAgents(): void
    {
        $admin = $this->seedAdmin();
        $fixtures = $this->seedAgentFixture();
        $client = $this->client;
        $client->loginUser($admin, 'main');

        $client->request('GET', '/admin/agents');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Party agents');
        self::assertStringContainsString($fixtures['agent']->getFullName(), $client->getResponse()->getContent());

        $client->request('GET', '/admin/agents/'.$fixtures['agent']->getId());
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Fixture Election', $client->getResponse()->getContent());

        $client->request('GET', '/admin/reports');
        self::assertResponseIsSuccessful();

        $client->request('GET', '/admin/assignments/'.$fixtures['assignment']->getId());
        self::assertResponseIsSuccessful();
    }

    public function testAdminRegistersAgentWithPasswordAndAgentLogsIn(): void
    {
        $admin = $this->seedAdmin();
        $fixtures = $this->seedAgentFixture();
        $client = $this->client;
        $client->loginUser($admin, 'main');

        $crawler = $client->request('GET', '/admin/agents/new');
        self::assertResponseIsSuccessful();

        $tokenField = $crawler->filter('#party_agent__token');
        self::assertCount(1, $tokenField);
        $token = $tokenField->attr('value');

        $client->request('POST', '/admin/agents/new', [
            'party_agent' => [
                '_token' => $token,
                'firstName' => 'Newly',
                'lastName' => 'Created',
                'email' => 'newly.created@example.org',
                'party' => (string) $fixtures['party']->getId(),
                'agentCode' => 'AGT-NEW-0001',
                'status' => 'active',
                'enabled' => '1',
                'password' => 'AgentPass789!',
            ],
        ]);

        self::assertResponseRedirects();

        $agent = $this->em->getRepository(\App\Entity\PartyAgent::class)
            ->findOneBy(['email' => 'newly.created@example.org']);
        self::assertNotNull($agent);
        self::assertSame('AGT-NEW-0001', $agent->getAgentCode());
    }
}
