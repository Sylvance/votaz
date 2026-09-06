<?php

namespace App\Controller;

use App\Entity\Enum\ThreadCategory;
use App\Entity\Post;
use App\Entity\Thread;
use App\Entity\Voter;
use App\Repository\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ForumController extends AbstractController
{
    #[Route('/forums', name: 'app_forum_index')]
    public function index(ThreadRepository $threadRepository, Request $request): Response
    {
        $category = $request->query->get('category');
        $category = $category !== null && '' !== $category ? ThreadCategory::tryFrom($category) : null;

        return $this->render('forum/index.html.twig', [
            'threads' => $threadRepository->findLatest($category),
            'categories' => ThreadCategory::cases(),
            'activeCategory' => $category,
        ]);
    }

    #[Route('/forums/new', name: 'app_forum_new')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VOTER');

        $thread = new Thread();
        $form = $this->createForm(\App\Form\ThreadType::class, $thread);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Voter $voter */
            $voter = $this->getUser();
            $thread->setAuthor($voter);
            $thread->setAuthorName($voter->getFullName());

            $base = $slugger->slug($thread->getTitle())->lower()->toString() ?: 'discussion';
            $slug = $base;
            $n = 2;
            while (null !== $em->getRepository(Thread::class)->findOneBy(['slug' => $slug])) {
                $slug = $base.'-'.$n++;
            }
            $thread->setSlug($slug);

            $em->persist($thread);
            $em->flush();

            $this->addFlash('success', 'Your discussion has been published.');

            return $this->redirectToRoute('app_forum_show', ['slug' => $thread->getSlug()]);
        }

        return $this->render('forum/new.html.twig', ['form' => $form]);
    }

    #[Route('/forums/{slug}', name: 'app_forum_show')]
    public function show(Thread $thread, Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createForm(\App\Form\PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_VOTER');

            if ($thread->isLocked()) {
                $this->addFlash('error', 'This discussion is locked.');
            } else {
                /** @var Voter $voter */
                $voter = $this->getUser();
                $post->setThread($thread);
                $post->setAuthor($voter);
                $post->setAuthorName($voter->getFullName());
                $em->persist($post);
                $em->flush();
                $this->addFlash('success', 'Your reply has been posted.');
            }

            return $this->redirectToRoute('app_forum_show', ['slug' => $thread->getSlug()]);
        }

        return $this->render('forum/show.html.twig', [
            'thread' => $thread,
            'form' => $form,
        ]);
    }
}