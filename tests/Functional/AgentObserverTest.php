<?php

namespace App\Tests\Functional;

class AgentObserverTest extends BaseWebTestCase
{
    public function testAgentDashboardListsActiveAssignment(): void
    {
        $fixtures = $this->seedAgentFixture();
        $client = $this->client;

        $client->loginUser($fixtures['agent'], 'agent');
        $crawler = $client->request('GET', '/agent');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Agent console');
        self::assertStringContainsString('Fixture Election', $crawler->text());
        self::assertStringContainsString('Fixture Station 1', $crawler->text());
        self::assertStringContainsString('Submit report', $crawler->text());
    }

    public function testAgentCannotSeeAnotherAgentsAssignment(): void
    {
        $fixtures = $this->seedAgentFixture();

        $other = new \App\Entity\PartyAgent();
        $other->setFirstName('Other');
        $other->setLastName('Agent');
        $other->setEmail('other.agent@example.org');
        $other->setParty($fixtures['party']);
        $other->setAgentCode('AGT-TEST-0002');
        $other->setPassword('test-password');
        $other->setEnabled(true);
        $this->em->persist($other);
        $this->em->flush();

        $client = $this->client;
        $client->loginUser($other, 'agent');
        $client->request('GET', '/agent/assignment/'.$fixtures['assignment']->getId());

        self::assertResponseStatusCodeSame(403);
    }

    public function testReportFormIsPrepopulatedWithElectionParties(): void
    {
        $fixtures = $this->seedAgentFixture();
        $client = $this->client;
        $client->loginUser($fixtures['agent'], 'agent');

        $crawler = $client->request('GET', '/agent/assignment/'.$fixtures['assignment']->getId().'/report/new');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Observed party tallies', $crawler->text());
        self::assertStringContainsString('Fixture Party', $crawler->text());
    }

    public function testSubmittingReportPersistsResultsAndCompletesAssignment(): void
    {
        $fixtures = $this->seedAgentFixture();
        $client = $this->client;
        $client->loginUser($fixtures['agent'], 'agent');

        $electionId = $fixtures['election']->getId();
        $partyId = $fixtures['party']->getId();

        $client->request('POST', '/agent/assignment/'.$fixtures['assignment']->getId().'/report/new', [
            'observation_report' => [
                '_token' => 'csrf-token',
                'description' => 'Orderly queue, count observed at close.',
                'votingStartObserved' => '1',
                'votingEndObserved' => '1',
                'irregularities' => '0',
                'estimatedTurnout' => '150',
                'results' => [
                    ['party' => (string) $partyId, 'observedVotes' => '61'],
                ],
            ],
        ], [], ['HTTP_ORIGIN' => 'http://localhost']);

        self::assertResponseRedirects('/agent/assignment/'.$fixtures['assignment']->getId());

        // Reload from DB to avoid in-memory managed state masks.
        $this->em->clear();
        $assignment = $this->em->getRepository(\App\Entity\AgentAssignment::class)->find($fixtures['assignment']->getId());

        self::assertSame(\App\Entity\Enum\AssignmentStatus::COMPLETED, $assignment->getStatus());

        $report = $assignment->getReports()->first();
        self::assertNotNull($report);
        self::assertSame(150, $report->getEstimatedTurnout());
        self::assertCount(1, $report->getResults());
        self::assertSame(61, $report->getResults()->first()->getObservedVotes());
    }

    public function testReportsOutOfScopeAreBlocked(): void
    {
        $client = $this->client;

        // An anonymous (non-agent) user must be redirected to the agent login.
        $client->request('GET', '/agent');
        self::assertResponseRedirects();

        $client->request('GET', '/admin/agents');
        self::assertResponseRedirects('/login');
    }
}
