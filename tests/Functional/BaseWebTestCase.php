<?php

namespace App\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Seeds an isolated schema into the test SQLite database and builds the tiny
 * fixture set the agent-observer feature tests rely on.
 */
abstract class BaseWebTestCase extends WebTestCase
{
    protected EntityManagerInterface $em;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $client = static::createClient();
        $this->em = static::getContainer()
            ->get('doctrine')
            ->getManager();

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->client = $client;
    }

    /**
     * Build a party, a voting election, an agent and an active assignment.
     */
    protected function seedAgentFixture(): array
    {
        $party = new \App\Entity\PoliticalParty();
        $party->setName('Fixture Party');
        $party->setAbbreviation('FP');
        $party->setRegistrationNumber('PRT-TEST-00001');
        $party->setLeaderName('Test Leader');
        $party->setStatus(\App\Entity\Enum\PartyStatus::APPROVED);
        $this->em->persist($party);

        $election = new \App\Entity\Election();
        $election->setName('Fixture Election');
        $election->setCode('FIXELE');
        $election->setVotingStartAt(new \DateTimeImmutable('-1 hour'));
        $election->setVotingEndAt(new \DateTimeImmutable('+5 hours'));
        $election->setStatus(\App\Entity\Enum\ElectionStatus::VOTING);

        foreach ([
            ['Fixture Candidate A', $party],
            ['Fixture Candidate B', $party],
        ] as $i => [$name, $candidateParty]) {
            $candidate = new \App\Entity\Candidate();
            $candidate->setElection($election);
            $candidate->setParty($candidateParty);
            $candidate->setFullName($name);
            $candidate->setBallotPosition($i + 1);
            $candidate->setStatus(\App\Entity\Enum\CandidateStatus::APPROVED);
            $this->em->persist($candidate);
        }
        $this->em->persist($election);

        $agent = new \App\Entity\PartyAgent();
        $agent->setFirstName('Observer');
        $agent->setLastName('Tester');
        $agent->setEmail('observer.tester@example.org');
        $agent->setParty($party);
        $agent->setAgentCode('AGT-TEST-0001');
        $agent->setPassword('test-password');
        $agent->setStatus(\App\Entity\Enum\PartyAgentStatus::ACTIVE);
        $agent->setEnabled(true);
        $this->em->persist($agent);

        $assignment = new \App\Entity\AgentAssignment();
        $assignment->setAgent($agent);
        $assignment->setElection($election);
        $assignment->setPollingStation('Fixture Station 1');
        $assignment->setAssignedBy('Commission');
        $assignment->setStatus(\App\Entity\Enum\AssignmentStatus::ACTIVE);
        $this->em->persist($assignment);

        $this->em->flush();
        $this->em->refresh($election);

        return [
            'party' => $party,
            'election' => $election,
            'agent' => $agent,
            'assignment' => $assignment,
        ];
    }

    protected function seedAdmin(): \App\Entity\Voter
    {
        $admin = new \App\Entity\Voter();
        $admin->setNationalId('NID-ADMIN-1');
        $admin->setFirstName('System');
        $admin->setLastName('Administrator');
        $admin->setEmail('admin@example.gov');
        $admin->setUsername('admin');
        $admin->setDateOfBirth(new \DateTimeImmutable('-40 years'));
        $admin->setGender('M');
        $admin->setPassword('x'); // loginUser() bypasses password verification
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setStatus(\App\Entity\Enum\VoterStatus::CONFIRMED);
        $admin->setVoterNumber('VOT-ADMIN-000000001');
        $this->em->persist($admin);
        $this->em->flush();

        return $admin;
    }
}