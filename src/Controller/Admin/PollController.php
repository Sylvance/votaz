<?php

namespace App\Controller\Admin;

use App\Entity\Poll;
use App\Form\PollType;
use App\Repository\PollRepository;
use App\Repository\PollResponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/polls', name: 'admin_poll_')]
class PollController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(PollRepository $pollRepository): Response
    {
        return $this->render('admin/polls/index.html.twig', [
            'polls' => $pollRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $poll = new Poll();
        $form = $this->createForm(PollType::class, $poll);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($poll);
            $em->flush();
            $this->addFlash('success', 'Poll created.');

            return $this->redirectToRoute('admin_poll_show', ['id' => $poll->getId()]);
        }

        return $this->render('admin/polls/form.html.twig', ['form' => $form, 'poll' => $poll]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(Poll $poll, PollResponseRepository $responseRepository): Response
    {
        return $this->render('admin/polls/show.html.twig', [
            'poll' => $poll,
            'results' => $responseRepository->surveyResults($poll),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Poll $poll, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PollType::class, $poll);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Poll updated.');

            return $this->redirectToRoute('admin_poll_show', ['id' => $poll->getId()]);
        }

        return $this->render('admin/polls/form.html.twig', ['form' => $form, 'poll' => $poll]);
    }

    #[Route('/{id}/publish', name: 'publish', requirements: ['id' => '\d+'])]
    public function publish(Poll $poll, EntityManagerInterface $em): Response
    {
        $poll->setPublishedAt(new \DateTimeImmutable());
        $poll->setStartsAt($poll->getStartsAt() ?? new \DateTimeImmutable());
        $em->flush();
        $this->addFlash('success', 'Poll published.');

        return $this->redirectToRoute('admin_poll_show', ['id' => $poll->getId()]);
    }
}