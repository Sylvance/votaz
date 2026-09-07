<?php

namespace App\Controller\Admin;

use App\Entity\Enum\VoterStatus;
use App\Repository\ElectionRegistrationRepository;
use App\Repository\ElectionRepository;
use App\Repository\ObservationReportRepository;
use App\Repository\PartyAgentRepository;
use App\Repository\PoliticalPartyRepository;
use App\Repository\VoterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'dashboard')]
    public function index(
        VoterRepository $voterRepository,
        ElectionRepository $electionRepository,
        PoliticalPartyRepository $partyRepository,
        PartyAgentRepository $agentRepository,
        ObservationReportRepository $reportRepository,
        ElectionRegistrationRepository $registrationRepository,
        EntityManagerInterface $em,
    ): Response {
        $voterCounts = $voterRepository->countByStatus();

        // Number of voters registered per active election.
        $elections = $electionRepository->findBy([], ['createdAt' => 'DESC'], 6);
        $electionRegistrationCounts = [];
        foreach ($elections as $election) {
            $electionRegistrationCounts[] = [
                'election' => $election,
                'count' => $registrationRepository->countForElection($election),
                'votedCount' => $registrationRepository->countForElection($election, \App\Entity\Enum\RegistrationStatus::VOTED),
            ];
        }

        return $this->render('admin/dashboard.html.twig', [
            'counts' => [
                'voters' => array_sum(array_values($voterCounts)),
                'confirmedVoters' => $voterCounts[VoterStatus::CONFIRMED->value] ?? 0,
                'elections' => array_sum(array_values($electionRepository->countByStatus())),
                'parties' => array_sum(array_values($partyRepository->countByStatus())),
                'agents' => array_sum(array_values($agentRepository->countByStatus())),
            ],
            'electionRegistrationCounts' => $electionRegistrationCounts,
            'recentIrregularities' => $reportRepository->findRecentWithIrregularities(5),
        ]);
    }
}
