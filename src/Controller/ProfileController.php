<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\Election;
use App\Entity\ElectionRegistration;
use App\Entity\Enum\RegistrationStatus;
use App\Repository\ElectionRegistrationRepository;
use App\Service\EligibilityService;
use App\Service\VoterRegistrationService;
use App\Service\VotingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(ElectionRegistrationRepository $registrationRepository): Response
    {
        $voter = $this->getUser();
        $registrations = $registrationRepository->findBy(['voter' => $voter], ['createdAt' => 'DESC']);

        $hasVoted = [];
        foreach ($registrations as $reg) {
            $hasVoted[$reg->getElection()->getId()] = RegistrationStatus::VOTED === $reg->getStatus();
        }

        return $this->render('profile/index.html.twig', [
            'voter' => $voter,
            'registrations' => $registrations,
            'hasVoted' => $hasVoted,
        ]);
    }

    #[Route('/profile/receipt/{id}', name: 'app_profile_receipt', requirements: ['id' => '\d+'])]
    public function receipt(ElectionRegistration $registration): Response
    {
        if ($registration->getVoter()->getId() !== $this->getUser()?->getId() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('profile/receipt.html.twig', ['registration' => $registration]);
    }

    #[Route('/elections/{id}/register', name: 'app_election_register', requirements: ['id' => '\d+'])]
    public function registerForElection(
        Election $election,
        ElectionRegistrationRepository $registrationRepository,
        EligibilityService $eligibility,
        VoterRegistrationService $registrationService,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_VOTER');
        $voter = $this->getUser();

        if ($registrationRepository->findOneByElectionAndVoter($election, $voter)) {
            $this->addFlash('info', 'You are already registered for this election.');

            return $this->redirectToRoute('app_election_show', ['id' => $election->getId()]);
        }

        if (!$election->isRegistrationOpen()) {
            $this->addFlash('error', 'Voter registration is not currently open for this election.');

            return $this->redirectToRoute('app_election_show', ['id' => $election->getId()]);
        }

        if (!$eligibility->isEligible($voter, $election)) {
            foreach ($eligibility->reasonsForIneligibility($voter, $election) as $reason) {
                $this->addFlash('error', $reason);
            }

            return $this->redirectToRoute('app_election_show', ['id' => $election->getId()]);
        }

        $registration = $registrationService->createElectionRegistration($voter, $election);
        $registrationService->approveElectionRegistration($registration);

        $this->addFlash('success', sprintf(
            'You are registered to vote in this election. Your receipt number is %s.',
            $registration->getReceiptNumber()
        ));

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/elections/{id}/vote', name: 'app_election_vote', requirements: ['id' => '\d+'])]
    public function vote(Election $election, Request $request, VotingService $votingService, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VOTER');
        $voter = $this->getUser();

        if ($request->isMethod('POST')) {
            $candidate = $em->getRepository(Candidate::class)->find((int) $request->request->get('candidate_id'));
            if (null === $candidate || $candidate->getElection()->getId() !== $election->getId()) {
                $this->addFlash('error', 'Invalid candidate selection.');

                return $this->redirectToRoute('app_election_vote', ['id' => $election->getId()]);
            }

            try {
                $votingService->castVote($voter, $election, $candidate);
                $this->addFlash('success', 'Your vote has been recorded. Thank you for participating.');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('app_election_show', ['id' => $election->getId()]);
        }

        if (!$votingService->canVote($voter, $election)) {
            $this->addFlash('error', 'You are not eligible to vote in this election right now.');

            return $this->redirectToRoute('app_election_show', ['id' => $election->getId()]);
        }

        return $this->render('election/vote.html.twig', [
            'election' => $election,
            'candidates' => $election->getApprovedCandidates(),
        ]);
    }
}
