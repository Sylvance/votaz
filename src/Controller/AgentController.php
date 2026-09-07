<?php

namespace App\Controller;

use App\Entity\AgentAssignment;
use App\Entity\Enum\AssignmentStatus;
use App\Entity\ObservationReport;
use App\Entity\ObservationResult;
use App\Form\ObservationReportType;
use App\Repository\AgentAssignmentRepository;
use App\Repository\ObservationReportRepository;
use App\Service\ObservationUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent', name: 'app_agent_')]
#[IsGranted('ROLE_AGENT')]
class AgentController extends AbstractController
{
    #[Route('', name: 'dashboard')]
    public function dashboard(AgentAssignmentRepository $assignmentRepository): Response
    {
        $agent = $this->getUser();

        $active = $assignmentRepository->findByAgent($agent, AssignmentStatus::ACTIVE);
        $past = $assignmentRepository->findByAgent($agent, AssignmentStatus::COMPLETED);

        return $this->render('agent/dashboard.html.twig', [
            'agent' => $agent,
            'activeAssignments' => $active,
            'pastAssignments' => $past,
        ]);
    }

    #[Route('/assignment/{id}', name: 'assignment_show', requirements: ['id' => '\d+'])]
    public function showAssignment(AgentAssignment $assignment, ObservationReportRepository $reportRepository): Response
    {
        $this->denyAccessUnlessGranted('AGENT_ASSIGNMENT', $assignment);

        return $this->render('agent/assignment.html.twig', [
            'assignment' => $assignment,
            'reports' => $reportRepository->findByAssignment($assignment),
        ]);
    }

    #[Route('/assignment/{id}/report/new', name: 'assignment_report', requirements: ['id' => '\d+'])]
    public function submitReport(
        AgentAssignment $assignment,
        Request $request,
        EntityManagerInterface $em,
        ObservationUploadService $uploadService,
    ): Response {
        $this->denyAccessUnlessGranted('AGENT_ASSIGNMENT', $assignment);

        if (AssignmentStatus::COMPLETED === $assignment->getStatus()) {
            $this->addFlash('error', 'This assignment is closed. No further reports can be submitted.');

            return $this->redirectToRoute('app_agent_assignment_show', ['id' => $assignment->getId()]);
        }

        $report = new ObservationReport();
        $report->setAssignment($assignment);

        // Pre-populate one result row per party in this election so the agent just fills in the numbers.
        $election = $assignment->getElection();
        if (null !== $election) {
            $seen = [];
            foreach ($election->getApprovedCandidates() as $candidate) {
                $party = $candidate->getParty();
                if (null === $party || isset($seen[$party->getId()])) {
                    continue;
                }
                $seen[$party->getId()] = true;
                $result = new ObservationResult();
                $result->setParty($party)->setObservedVotes(0);
                $report->addResult($result);
            }
        }

        $form = $this->createForm(ObservationReportType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $files = $form->get('photos')->getData() ?? [];
            $uploadService->attachPhotos($report, $files);

            if (!$report->isIrregularities()) {
                $report->setIrregularityDetails(null);
            }

            // Drop rows with zero votes so the agent's default blanks don't pollute the record.
            foreach ($report->getResults() as $result) {
                if (0 === $result->getObservedVotes()) {
                    $report->removeResult($result);
                }
            }

            $em->persist($report);
            $em->flush();

            $assignment->setStatus(AssignmentStatus::COMPLETED);
            $em->flush();

            $this->addFlash('success', 'Report submitted. Thank you for observing.');

            return $this->redirectToRoute('app_agent_assignment_show', ['id' => $assignment->getId()]);
        }

        return $this->render('agent/report_form.html.twig', [
            'assignment' => $assignment,
            'form' => $form,
        ]);
    }
}