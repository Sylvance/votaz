<?php

namespace App\Controller;

use App\Entity\Enum\ElectionStatus;
use App\Entity\Poll;
use App\Repository\ElectionRepository;
use App\Repository\PoliticalPartyRepository;
use App\Repository\PollRepository;
use App\Repository\RoundTableRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ElectionRepository $electionRepository,
        PollRepository $pollRepository,
        RoundTableRepository $roundTableRepository,
        PoliticalPartyRepository $partyRepository,
    ): Response {
        $upcomingElections = $electionRepository->findByStatuses([
            ElectionStatus::REGISTRATION_OPEN,
            ElectionStatus::NOMINATION,
            ElectionStatus::VOTING,
        ]);

        $recentPolls = $pollRepository->findOpenAndPast();
        $upcomingRoundTables = $roundTableRepository->findUpcoming();
        $parties = $partyRepository->findByStatus();

        usort($recentPolls, static fn (Poll $a, Poll $b): int => $b->getCreatedAt() <=> $a->getCreatedAt());
        $recentPolls = \array_slice($recentPolls, 0, 5);

        return $this->render('home/index.html.twig', [
            'upcomingElections' => $upcomingElections,
            'recentPolls' => $recentPolls,
            'upcomingRoundTables' => $upcomingRoundTables,
            'parties' => $parties,
        ]);
    }
}
