<?php

namespace App\Controller\Admin;

use App\Entity\Candidate;
use App\Entity\Election;
use App\Entity\Enum\CandidateStatus;
use App\Form\CandidateType;
use App\Repository\CandidateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/elections/{id}/candidates', name: 'admin_candidate_')]
class CandidateController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Election $election, CandidateRepository $candidateRepository): Response
    {
        return $this->render('admin/candidates/index.html.twig', [
            'election' => $election,
            'candidates' => $candidateRepository->findByElection($election, null),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Election $election, Request $request, EntityManagerInterface $em): Response
    {
        $candidate = new Candidate();
        $candidate->setElection($election);

        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($candidate);
            $em->flush();
            $this->addFlash('success', 'Candidate added.');

            return $this->redirectToRoute('admin_candidate_index', ['id' => $election->getId()]);
        }

        return $this->render('admin/candidates/form.html.twig', [
            'form' => $form,
            'election' => $election,
        ]);
    }

    #[Route('/{candidateId}/edit', name: 'edit', requirements: ['id' => '\d+', 'candidateId' => '\d+'])]
    public function edit(Election $election, Candidate $candidate, Request $request, EntityManagerInterface $em): Response
    {
        if ($candidate->getElection()->getId() !== $election->getId()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Candidate updated.');

            return $this->redirectToRoute('admin_candidate_index', ['id' => $election->getId()]);
        }

        return $this->render('admin/candidates/form.html.twig', [
            'form' => $form,
            'election' => $election,
        ]);
    }

    #[Route('/{candidateId}/set-status/{status}', name: 'set_status', requirements: ['candidateId' => '\d+'])]
    public function setStatus(Candidate $candidate, string $status, Election $election, EntityManagerInterface $em): Response
    {
        if ($candidate->getElection()->getId() !== $election->getId()) {
            throw $this->createNotFoundException();
        }
        $candidate->setStatus(CandidateStatus::from($status));
        $em->flush();
        $this->addFlash('success', 'Candidate status updated.');

        return $this->redirectToRoute('admin_candidate_index', ['id' => $election->getId()]);
    }
}