<?php

namespace App\Controller\Admin;

use App\Entity\Election;
use App\Entity\ElectionRegistration;
use App\Entity\Enum\CandidateStatus;
use App\Entity\Enum\ElectionStatus;
use App\Entity\Enum\RegistrationStatus;
use App\Form\ElectionTypeForm;
use App\Repository\CandidateRepository;
use App\Repository\ElectionRepository;
use App\Repository\ElectionRegistrationRepository;
use App\Service\ResultsService;
use App\Service\VoterRegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/elections', name: 'admin_election_')]
class ElectionController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(ElectionRepository $electionRepository): Response
    {
        return $this->render('admin/elections/index.html.twig', [
            'elections' => $electionRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $election = new Election();
        $form = $this->createForm(ElectionTypeForm::class, $election);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $election->setCode(strtoupper(substr(\bin2hex(\random_bytes(6)), 0, 8)));
            $em->persist($election);
            $em->flush();
            $this->addFlash('success', 'Election created.');

            return $this->redirectToRoute('admin_election_show', ['id' => $election->getId()]);
        }

        return $this->render('admin/elections/form.html.twig', [
            'form' => $form,
            'title' => 'New election',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Election $election, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ElectionTypeForm::class, $election);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Election updated.');

            return $this->redirectToRoute('admin_election_show', ['id' => $election->getId()]);
        }

        return $this->render('admin/elections/form.html.twig', [
            'form' => $form,
            'title' => 'Edit election',
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(
        Election $election,
        CandidateRepository $candidateRepository,
        ElectionRegistrationRepository $registrationRepository,
        ResultsService $resultsService,
    ): Response {
        $candidates = $candidateRepository->findByElection($election, null);
        $tally = ElectionStatus::RESULTS_PUBLISHED === $election->getStatus()
            ? $resultsService->tally($election)
            : null;

        return $this->render('admin/elections/show.html.twig', [
            'election' => $election,
            'candidates' => $candidates,
            'voterCount' => $registrationRepository->countForElection($election),
            'approvedCount' => $registrationRepository->countForElection($election, RegistrationStatus::ELIGIBLE),
            'votedCount' => $registrationRepository->countForElection($election, RegistrationStatus::VOTED),
            'tally' => $tally,
        ]);
    }

    #[Route('/{id}/voters', name: 'voters', requirements: ['id' => '\d+'])]
    public function voters(
        Election $election,
        ElectionRegistrationRepository $registrationRepository,
        Request $request,
        VoterRegistrationService $registrationService,
    ): Response {
        $term = $request->query->get('term', null);
        $registrations = $registrationRepository->findByElection($election, null, $term);

        return $this->render('admin/elections/voters.html.twig', [
            'election' => $election,
            'registrations' => $registrations,
            'term' => $term,
        ]);
    }

    #[Route('/{id}/voters/{registrationId}/approve', name: 'voter_approve', requirements: ['id' => '\d+', 'registrationId' => '\d+'])]
    public function approveVoter(
        ElectionRegistration $registration,
        VoterRegistrationService $registrationService,
    ): Response {
        $registrationService->approveElectionRegistration($registration);
        $this->addFlash('success', sprintf('Approved %s. Receipt: %s', $registration->getVoter()->getVoterNumber(), $registration->getReceiptNumber()));

        return $this->redirectToRoute('admin_election_voters', ['id' => $registration->getElection()->getId()]);
    }

    #[Route('/{id}/voters/{registrationId}/reject', name: 'voter_reject', requirements: ['id' => '\d+', 'registrationId' => '\d+'])]
    public function rejectVoter(ElectionRegistration $registration, Request $request, VoterRegistrationService $registrationService): Response
    {
        $reason = $request->request->get('reason', 'Not eligible');
        $registrationService->rejectElectionRegistration($registration, (string) $reason);
        $this->addFlash('success', 'Registration rejected.');

        return $this->redirectToRoute('admin_election_voters', ['id' => $registration->getElection()->getId()]);
    }

    #[Route('/{id}/publish-results', name: 'publish_results', requirements: ['id' => '\d+'])]
    public function publishResults(Election $election, EntityManagerInterface $em): Response
    {
        $election->setStatus(ElectionStatus::RESULTS_PUBLISHED);
        $election->setResultsPublishedAt(new \DateTimeImmutable());
        $em->flush();
        $this->addFlash('success', 'Election results published.');

        return $this->redirectToRoute('admin_election_show', ['id' => $election->getId()]);
    }
}