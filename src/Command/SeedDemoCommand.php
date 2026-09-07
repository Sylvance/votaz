<?php

namespace App\Command;

use App\Entity\AgentAssignment;
use App\Entity\Candidate;
use App\Entity\District;
use App\Entity\Election;
use App\Entity\Enum\AssignmentStatus;
use App\Entity\Enum\CandidateStatus;
use App\Entity\Enum\ElectionStatus;
use App\Entity\Enum\ElectionType;
use App\Entity\Enum\PartyAgentStatus;
use App\Entity\Enum\PartyStatus;
use App\Entity\Enum\QuestionType;
use App\Entity\Enum\RoundTableStatus;
use App\Entity\Enum\ThreadCategory;
use App\Entity\Enum\VoterStatus;
use App\Entity\Manifesto;
use App\Entity\ManifestoSection;
use App\Entity\PartyAgent;
use App\Entity\PoliticalParty;
use App\Entity\Poll;
use App\Entity\PollOption;
use App\Entity\PollQuestion;
use App\Entity\RoundTable;
use App\Entity\Thread;
use App\Entity\Voter;
use App\Service\CodeGenerator;
use App\Service\VoterRegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-demo',
    description: 'Seed the database with demonstration districts, parties, elections, polls and forum content',
)]
class SeedDemoCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VoterRegistrationService $registrationService,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $now = new \DateTimeImmutable();

        if (0 === $this->em->getRepository(District::class)->count([])) {
            $districts = $this->seedDistricts();
            $io->note('Created districts.');
        } else {
            $districts = $this->em->getRepository(District::class)->findAll();
        }

        $parties = $this->em->getRepository(PoliticalParty::class)->findAll();
        if ([] === $parties) {
            $parties = $this->seedParties($now);
            $io->note('Created political parties.');
        }

        $elections = $this->em->getRepository(Election::class)->findAll();
        if ([] === $elections) {
            $elections = $this->seedElections($districts, $parties, $now);
            $io->note('Created elections and candidates.');
        }

        if (0 === $this->em->getRepository(Voter::class)->count([])) {
            $this->seedVoters($districts, $now);
            $io->note('Created sample voters.');
        }

        if (0 === $this->em->getRepository(Poll::class)->count([])) {
            $this->seedPolls($now);
            $io->note('Created polls and surveys.');
        }

        if (0 === $this->em->getRepository(RoundTable::class)->count([])) {
            $this->seedRoundTables($elections, $now);
            $io->note('Created round tables.');
        }

        if (0 === $this->em->getRepository(Thread::class)->count([])) {
            $this->seedForum();
            $io->note('Created forum discussions.');
        }

        if (0 === $this->em->getRepository(PartyAgent::class)->count([])) {
            $this->seedAgents($parties, $elections);
            $io->note('Created party agents and poll assignments.');
        }

        $io->success('Demo data seeded.');

        return Command::SUCCESS;
    }

    /**
     * @return District[]
     */
    private function seedDistricts(): array
    {
        $districts = [];
        foreach ([
            ['NORTH', 'North District'],
            ['SOUTH', 'South District'],
            ['EAST', 'East District'],
            ['WEST', 'West District'],
            ['CENTRAL', 'Central District'],
        ] as [$code, $name]) {
            $district = new District();
            $district->setCode($code);
            $district->setName($name);
            $this->em->persist($district);
            $districts[] = $district;
        }
        $this->em->flush();

        return $districts;
    }

    /**
     * @return PoliticalParty[]
     */
    private function seedParties(\DateTimeImmutable $now): array
    {
        $parties = [];
        foreach ([
            ['People\'s Democratic Movement', 'PDM', 'Unity House, 1 Independence Ave', 'DEMOCRACY', 3],
            ['United Prosperity Party', 'UPP', 'Prosperity Towers, 22 Market St', 'PROSPERITY', 2],
            ['Green Alliance', 'GA', 'Eco House, 5 River Rd', 'SUSTAINABILITY', 1],
            ['Justice & Equality', 'J&E', 'Liberty Plaza, 9 Justice Way', 'EQUALITY', 4],
        ] as [$name, $abbr, $address, $motto, $leader]) {
            $party = new PoliticalParty();
            $party->setName($name);
            $party->setAbbreviation($abbr);
            $party->setDescription(sprintf('%s is a registered political party seeking to represent the interests of all citizens.', $name));
            $party->setRegistrationNumber(CodeGenerator::partyRegistrationNumber());
            $party->setLeaderName(['Amara Nnaji', 'Kofi Mensah', 'Leila Osman', 'David Chen'][$leader - 1]);
            $party->setLeaderTitle(['National Chairperson', 'President', 'Leader', 'Chairwoman'][$leader - 1]);
            $party->setLeaderAnnouncedAt($now->modify(sprintf('-%d days', $leader * 30)));
            $party->setMotto($motto);
            $party->setStatus(PartyStatus::APPROVED);
            $party->setRegisteredAt($now->modify('-300 days'));

            $manifesto = new Manifesto();
            $manifesto->setParty($party);
            $manifesto->setTitle($party->getName().' Manifesto '.$now->format('Y'));
            $manifesto->setSummary(str_repeat($motto.', '.$motto.'. ', 3));
            $manifesto->setIsPublished(true);
            $manifesto->setPublishedAt($now->modify('-20 days'));

            $sections = [
                ['Governance & Rule of Law', 'Strengthen independent institutions, fight corruption, and ensure transparent elections.'],
                ['Economy', 'Drive inclusive growth, support small businesses, and create sustainable jobs.'],
                ['Healthcare', 'Expand universal health coverage and invest in primary care clinics.'],
                ['Education', 'Modernize schools, train teachers, and expand free higher education.'],
            ];
            foreach ($sections as $i => [$title, $content]) {
                $section = new ManifestoSection();
                $section->setManifesto($manifesto);
                $section->setTitle($title);
                $section->setContent($content);
                $section->setPosition($i + 1);
                $manifesto->addSection($section);
                $this->em->persist($section);
            }
            $this->em->persist($manifesto);
            $this->em->persist($party);
            $parties[] = $party;
        }
        $this->em->flush();

        return $parties;
    }

    /**
     * @param District[]       $districts
     * @param PoliticalParty[] $parties
     *
     * @return Election[]
     */
    private function seedElections(array $districts, array $parties, \DateTimeImmutable $now): array
    {
        $elections = [];

        // A national election currently in the registration + nomination phase.
        $general = new Election();
        $general->setName('National General Election '.$now->format('Y'));
        $general->setCode(strtoupper('GEN'.(string) $now->format('Y')));
        $general->setType(ElectionType::GENERAL);
        $general->setDescription('General election to elect the national executive and legislature.');
        $general->setRegistrationStartAt($now->modify('-15 days'));
        $general->setRegistrationEndAt($now->modify('+15 days'));
        $general->setNominationStartAt($now->modify('-5 days'));
        $general->setNominationEndAt($now->modify('+20 days'));
        $general->setVotingStartAt($now->modify('+30 days'));
        $general->setVotingEndAt($now->modify('+31 days'));
        $general->setStatus(ElectionStatus::REGISTRATION_OPEN);
        $this->em->persist($general);
        $elections[] = $general;

        // A district by-election shortly after the general election.
        $byElection = new Election();
        $byElection->setName('Central District By-Election');
        $byElection->setCode(strtoupper('BY'.(string) $now->format('Y').'1'));
        $byElection->setType(ElectionType::BY_ELECTION);
        $byElection->setDistrict($districts[4]);
        $byElection->setDescription('By-election to fill the vacancy caused by the resignation of the Central District representative.');
        $byElection->setReason('Resignation of the sitting representative.');
        $byElection->setRegistrationStartAt($now->modify('+45 days'));
        $byElection->setRegistrationEndAt($now->modify('+60 days'));
        $byElection->setNominationStartAt($now->modify('+55 days'));
        $byElection->setNominationEndAt($now->modify('+65 days'));
        $byElection->setVotingStartAt($now->modify('+90 days'));
        $byElection->setVotingEndAt($now->modify('+91 days'));
        $byElection->setStatus(ElectionStatus::DRAFT);
        $this->em->persist($byElection);
        $elections[] = $byElection;

        // A past election with published results.
        $past = new Election();
        $past->setName('Mid-Term Snap Poll '.$now->modify('-1 year')->format('Y'));
        $past->setCode(strtoupper('SNP'.$now->modify('-1 year')->format('Y')));
        $past->setType(ElectionType::GENERAL);
        $past->setDescription('Snap election held one year ago.');
        $past->setRegistrationStartAt($now->modify('-1 year')->modify('-30 days'));
        $past->setRegistrationEndAt($now->modify('-1 year')->modify('-10 days'));
        $past->setNominationStartAt($now->modify('-1 year')->modify('-20 days'));
        $past->setNominationEndAt($now->modify('-1 year')->modify('-5 days'));
        $past->setVotingStartAt($now->modify('-1 year'));
        $past->setVotingEndAt($now->modify('-1 year')->modify('+1 day'));
        $past->setResultsPublishedAt($now->modify('-1 year')->modify('+2 days'));
        $past->setStatus(ElectionStatus::RESULTS_PUBLISHED);
        $this->em->persist($past);
        $elections[] = $past;

        $this->seedCandidates($general, $parties, $now);
        $this->seedCandidates($past, $parties, $now->modify('-1 year'));
        $this->seedCandidates($byElection, [$parties[0], $parties[1]], $now);

        $this->em->flush();

        return $elections;
    }

    /**
     * @param PoliticalParty[] $parties
     */
    private function seedCandidates(Election $election, array $parties, \DateTimeImmutable $now): void
    {
        $names = [
            'Amina Bello', 'Joseph Okafor', 'Maria Santos', 'Henry Adeyemi', 'Sofia Kimani',
            'James Mwangi', 'Fatima Diallo', 'Peter Njoroge', 'Grace Osei', 'Ahmed Hassan',
        ];

        $ballot = 1;
        foreach ($parties as $i => $party) {
            $candidate = new Candidate();
            $candidate->setElection($election);
            $candidate->setParty($party);
            $candidate->setFullName($names[$i]);
            $candidate->setMotto($party->getMotto());
            $candidate->setBio(sprintf('%s candidate standing on a platform of %s.', $party->getName(), strtolower($party->getMotto() ?? 'public service')));
            $candidate->setBallotPosition($ballot++);
            $candidate->setStatus(CandidateStatus::APPROVED);
            $candidate->setDeclaredAt($now->modify('-10 days'));
            $this->em->persist($candidate);
        }

        if (ElectionStatus::RESULTS_PUBLISHED === $election->getStatus()) {
            $voters = $this->em->getRepository(Voter::class)->findBy(['status' => VoterStatus::CONFIRMED], null, 5);
            foreach ($voters as $vi => $voter) {
                $candidates = $this->em->getRepository(Candidate::class)->findByElection($election, CandidateStatus::APPROVED);
                $winner = $candidates[$vi % count($candidates)];
                $reg = $this->em->getRepository(\App\Entity\ElectionRegistration::class)->findOneByElectionAndVoter($election, $voter);
                if (null === $reg) {
                    $reg = $this->registrationService->createElectionRegistration($voter, $election);
                    $this->registrationService->approveElectionRegistration($reg);
                }
                $vote = new \App\Entity\Vote();
                $vote->setElection($election);
                $vote->setVoter($voter);
                $vote->setCandidate($winner);
                $this->em->persist($vote);
                $reg->setStatus(\App\Entity\Enum\RegistrationStatus::VOTED);
            }
        }
    }

    /**
     * @param District[] $districts
     */
    private function seedVoters(array $districts, \DateTimeImmutable $now): void
    {
        $first = ['Amina', 'Joseph', 'Maria', 'Henry', 'Sofia', 'James', 'Fatima', 'Peter', 'Grace', 'Ahmed'];
        $last = ['Bello', 'Okafor', 'Santos', 'Adeyemi', 'Kimani', 'Mwangi', 'Diallo', 'Njoroge', 'Osei', 'Hassan'];

        foreach ($first as $i => $name) {
            $voter = new Voter();
            $voter->setNationalId(sprintf('NID-%08d', $i + 1001));
            $voter->setFirstName($name);
            $voter->setLastName($last[$i]);
            $voter->setGender((0 === $i % 2) ? 'F' : 'M');
            $voter->setDateOfBirth(new \DateTimeImmutable(sprintf('-%d years', 25 + $i)));
            $voter->setEmail(strtolower($name.'.'.$last[$i]).'@example.gov');
            $voter->setPhone(sprintf('+155512%04d', $i + 100));
            $voter->setCity('Springfield');
            $voter->setRegion('Central Region');
            $voter->setDistrict($districts[$i % count($districts)]);
            $voter->setVoterNumber(CodeGenerator::voterNumber());
            $voter->setUsername($voter->getEmail());
            $voter->setPassword(password_hash('VoterPass123!', PASSWORD_DEFAULT));
            $voter->setRoles(['ROLE_VOTER']);
            $voter->setStatus(VoterStatus::CONFIRMED);
            $voter->setConfirmedAt($now->modify('-5 days'));
            $voter->setConfirmationCode(CodeGenerator::confirmationCode());
            $voter->setTermsAcceptedAt($now->modify('-5 days'));
            $this->em->persist($voter);
        }
        $this->em->flush();
    }

    private function seedPolls(\DateTimeImmutable $now): void
    {
        $poll = new Poll();
        $poll->setTitle('Which issue should candidates prioritise next election?');
        $poll->setDescription('Tell us what matters most to you. Results help direct the national conversation.');
        $poll->setStartsAt($now->modify('-7 days'));
        $poll->setEndsAt($now->modify('+23 days'));
        $poll->setRequiresAuth(true);
        $poll->setShowResultsAfterEnd(true);

        $question = new PollQuestion();
        $question->setPoll($poll);
        $question->setTitle('What is the single most important issue facing the country today?');
        $question->setType(QuestionType::SINGLE_CHOICE);
        $question->setPosition(1);
        $question->setRequired(true);
        foreach (['Economy & jobs', 'Healthcare', 'Education', 'Corruption', 'Security'] as $i => $label) {
            $option = new PollOption();
            $option->setQuestion($question);
            $option->setLabel($label);
            $option->setPosition($i + 1);
            $question->addOption($option);
            $this->em->persist($option);
        }
        $poll->addQuestion($question);
        $this->em->persist($question);
        $this->em->persist($poll);

        $survey = new Poll();
        $survey->setTitle('Citizen satisfaction survey');
        $survey->setDescription('Help us measure public confidence in the electoral process.');
        $survey->setIsSurvey(true);
        $survey->setStartsAt($now->modify('-2 days'));
        $survey->setEndsAt($now->modify('+60 days'));
        $survey->setRequiresAuth(true);
        $survey->setShowResultsAfterEnd(false);

        foreach ([
            ['How confident are you in the independence of the electoral commission?', ['Very confident', 'Somewhat confident', 'Not confident']],
            ['How easy was it to register as a voter?', ['Very easy', 'Easy', 'Difficult', 'Have not registered']],
        ] as $qi => [$text, $labels]) {
            $q = new PollQuestion();
            $q->setPoll($survey);
            $q->setTitle($text);
            $q->setType(QuestionType::SINGLE_CHOICE);
            $q->setPosition($qi + 1);
            $q->setRequired(true);
            foreach ($labels as $oi => $label) {
                $option = new PollOption();
                $option->setQuestion($q);
                $option->setLabel($label);
                $option->setPosition($oi + 1);
                $q->addOption($option);
                $this->em->persist($option);
            }
            $survey->addQuestion($q);
            $this->em->persist($q);
        }
        $this->em->persist($survey);

        $this->em->flush();
    }

    /**
     * @param Election[] $elections
     */
    private function seedRoundTables(array $elections, \DateTimeImmutable $now): void
    {
        foreach ([
            ['Open round table: National electoral reform', $elections[0], true, 5],
            ['Town hall: what to expect on polling day', $elections[0], false, 12],
            ['Youth forum on civic participation', null, true, 20],
        ] as [$title, $election, $online, $days]) {
            $rt = new RoundTable();
            $rt->setTitle($title);
            $rt->setDescription('An open, moderated discussion where citizens, candidates and officials can engage directly.');
            $rt->setElection($election);
            $rt->setTopic('Civic engagement and electoral transparency');
            $rt->setScheduledAt($now->modify('+'.$days.' days')->setTime(10, 0));
            $rt->setEndsAt($now->modify('+'.$days.' days')->setTime(12, 0));
            $rt->setIsOnline($online);
            $rt->setLocation($online ? 'https://meet.example.gov/roundtable' : 'City Hall, Constitutional Square');
            $rt->setHostName('Electoral Commission Secretariat');
            $rt->setStatus(RoundTableStatus::SCHEDULED);
            $this->em->persist($rt);
        }
        $this->em->flush();
    }

    private function seedForum(): void
    {
        $voters = $this->em->getRepository(Voter::class)->findBy(['status' => VoterStatus::CONFIRMED], null, 5);
        $participant = static function (int $i) use ($voters): Voter {
            return $voters[$i % count($voters)];
        };

        $threads = [
            ['What would fair debate rules look like to you?', 'Citizens should get equal speaking time in TV debates. What format do you prefer?', ThreadCategory::GENERAL, 1],
            ['Independent candidates: level playing field?', 'How should the commission support independent candidates in standing for election?', ThreadCategory::GENERAL, 2],
        ];

        foreach ($threads as $i => [$title, $content, $category, $authorIdx]) {
            $thread = new Thread();
            $thread->setTitle($title);
            $thread->setSlug((new \Symfony\Component\String\Slugger\AsciiSlugger())->slug($title)->lower()->toString().'-'.$i);
            $thread->setContent($content);
            $thread->setCategory($category);
            $author = $participant($authorIdx);
            $thread->setAuthor($author);
            $thread->setAuthorName($author->getFullName());
            $this->em->persist($thread);

            foreach ([
                'I think a citizens assembly is the best way to agree the rules.',
                'Transparency of funding matters just as much as airtime.',
                'Let us hear from young voters in every region, not just the capital.',
            ] as $j => $reply) {
                $post = new \App\Entity\Post();
                $post->setThread($thread);
                $post->setContent($reply);
                $replyAuthor = $participant($authorIdx + $j + 1);
                $post->setAuthor($replyAuthor);
                $post->setAuthorName($replyAuthor->getFullName());
                $this->em->persist($post);
            }
        }
        $this->em->flush();
    }

    /**
     * @param PoliticalParty[] $parties
     * @param Election[]       $elections
     */
    private function seedAgents(array $parties, array $elections): void
    {
        $names = [
            ['Zainab', 'Ibrahim', 'zainab.ibrahim@example.org'],
            ['Samuel', 'Cole', 'samuel.cole@example.org'],
            ['Ruth', 'Awuni', 'ruth.awuni@example.org'],
            ['Emeka', 'Nwosu', 'emeka.nwosu@example.org'],
        ];

        foreach ($names as $i => [$first, $last, $email]) {
            $agent = new PartyAgent();
            $agent->setFirstName($first);
            $agent->setLastName($last);
            $agent->setEmail($email);
            $agent->setPhone(sprintf('+1555127%04d', $i + 200));
            $agent->setParty($parties[$i % count($parties)]);
            $agent->setAgentCode(CodeGenerator::agentCode());
            $agent->setCredentials('Party accreditation badge #'.($i + 1));
            $agent->setPassword($this->passwordHasher->hashPassword($agent, 'AgentPass123!'));
            $agent->setStatus(PartyAgentStatus::ACTIVE);
            $agent->setEnabled(true);
            $this->em->persist($agent);

            if (isset($elections[0])) {
                $assignment = new AgentAssignment();
                $assignment->setAgent($agent);
                $assignment->setElection($elections[0]);
                $assignment->setDistrict($elections[0]->getDistrict());
                $assignment->setPollingStation('Central Hall, Unit '.($i + 1));
                $assignment->setAssignedBy('Electoral Commission Secretariat');
                $assignment->setStatus(AssignmentStatus::ACTIVE);
                $this->em->persist($assignment);
            }
        }

        $this->em->flush();
    }
}
