<?php

namespace App\Controller\Admin;

use App\Entity\Enum\PartyAgentStatus;
use App\Entity\PartyAgent;
use App\Form\AgentAssignmentType;
use App\Form\PartyAgentType;
use App\Repository\AgentAssignmentRepository;
use App\Repository\PartyAgentRepository;
use App\Service\CodeGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/agents', name: 'admin_agent_')]
class AgentController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(PartyAgentRepository $agentRepository): Response
    {
        return $this->render('admin/agents/index.html.twig', [
            'agents' => $agentRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $agent = new PartyAgent();
        $agent->setAgentCode(CodeGenerator::agentCode());

        $form = $this->createForm(PartyAgentType::class, $agent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $agent->setPassword($passwordHasher->hashPassword($agent, $plainPassword));

            $em->persist($agent);
            $em->flush();

            $this->addFlash('success', sprintf('Agent %s registered.', $agent->getFullName()));

            return $this->redirectToRoute('admin_agent_show', ['id' => $agent->getId()]);
        }

        return $this->render('admin/agents/form.html.twig', ['form' => $form, 'agent' => $agent]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(PartyAgent $agent, AgentAssignmentRepository $assignmentRepository): Response
    {
        return $this->render('admin/agents/show.html.twig', [
            'agent' => $agent,
            'assignments' => $assignmentRepository->findByAgent($agent),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(PartyAgent $agent, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PartyAgentType::class, $agent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($agent->isEnabled()) {
                $agent->setStatus(PartyAgentStatus::ACTIVE);
            }
            $em->flush();
            $this->addFlash('success', 'Agent updated.');

            return $this->redirectToRoute('admin_agent_show', ['id' => $agent->getId()]);
        }

        return $this->render('admin/agents/form.html.twig', ['form' => $form, 'agent' => $agent]);
    }

    #[Route('/{id}/assign', name: 'assign', requirements: ['id' => '\d+'])]
    public function assign(PartyAgent $agent, Request $request, EntityManagerInterface $em): Response
    {
        $assignment = new \App\Entity\AgentAssignment();
        $assignment->setAgent($agent);
        $assignment->setAssignedBy($this->getUser()?->getUserIdentifier() ?? 'admin');

        $form = $this->createForm(AgentAssignmentType::class, $assignment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($assignment);
            $em->flush();
            $this->addFlash('success', sprintf('%s assigned to %s.', $agent->getFullName(), $assignment->getElection()?->getName()));

            return $this->redirectToRoute('admin_agent_show', ['id' => $agent->getId()]);
        }

        return $this->render('admin/agents/assign.html.twig', ['form' => $form, 'agent' => $agent]);
    }
}