<?php

namespace App\Controller\Admin;

use App\Entity\AgentAssignment;
use App\Entity\Enum\AssignmentStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/assignments', name: 'admin_assignment_')]
class AssignmentController extends AbstractController
{
    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(AgentAssignment $assignment): Response
    {
        return $this->render('admin/assignments/show.html.twig', ['assignment' => $assignment]);
    }

    #[Route('/{id}/status/{status}', name: 'status', requirements: ['id' => '\d+', 'status' => 'active|completed|cancelled'])]
    public function setStatus(AgentAssignment $assignment, string $status, EntityManagerInterface $em): Response
    {
        $assignment->setStatus(AssignmentStatus::from($status));
        $em->flush();
        $this->addFlash('success', sprintf('Assignment status updated to %s.', $status));

        return $this->redirectToRoute('admin_assignment_show', ['id' => $assignment->getId()]);
    }
}
