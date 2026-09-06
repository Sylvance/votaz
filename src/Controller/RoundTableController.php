<?php

namespace App\Controller;

use App\Entity\RoundTable;
use App\Entity\RoundTableRegistration;
use App\Entity\Voter;
use App\Entity\Thread;
use App\Entity\Enum\ThreadCategory;
use App\Repository\RoundTableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RoundTableController extends AbstractController
{
    #[Route('/round-tables', name: 'app_roundtable_index')]
    public function index(RoundTableRepository $repository): Response
    {
        return $this->render('round_table/index.html.twig', [
            'roundTables' => $repository->findUpcoming(),
            'past' => array_reverse($repository->findBy([], ['scheduledAt' => 'DESC'], 10, 0)),
        ]);
    }

    #[Route('/round-tables/{id}', name: 'app_roundtable_show', requirements: ['id' => '\d+'])]
    public function show(RoundTable $roundTable, EntityManagerInterface $em): Response
    {
        $registration = null;
        if ($this->getUser() instanceof Voter) {
            $registration = $em->getRepository(RoundTableRegistration::class)
                ->findOneBy(['roundTable' => $roundTable, 'voter' => $this->getUser()]);
        }

        return $this->render('round_table/show.html.twig', [
            'roundTable' => $roundTable,
            'registration' => $registration,
            'discussion' => $roundTable->getThreads()->count() > 0 ? $roundTable->getThreads()->first() : null,
        ]);
    }

    #[Route('/round-tables/{id}/register', name: 'app_roundtable_register', requirements: ['id' => '\d+'])]
    public function register(RoundTable $roundTable, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VOTER');
        /** @var Voter $voter */
        $voter = $this->getUser();

        $existing = $em->getRepository(RoundTableRegistration::class)
            ->findOneBy(['roundTable' => $roundTable, 'voter' => $voter]);

        if (null !== $existing) {
            $this->addFlash('info', 'You are already registered to attend this round table.');
        } else {
            $registration = new RoundTableRegistration();
            $registration->setRoundTable($roundTable);
            $registration->setVoter($voter);
            $em->persist($registration);
            $em->flush();
            $this->addFlash('success', 'Your seat at this round table has been confirmed.');
        }

        return $this->redirectToRoute('app_roundtable_show', ['id' => $roundTable->getId()]);
    }

    #[Route('/round-tables/{id}/discuss', name: 'app_roundtable_discuss', requirements: ['id' => '\d+'])]
    public function discuss(RoundTable $roundTable, EntityManagerInterface $em, \Symfony\Component\String\Slugger\SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VOTER');
        /** @var Voter $voter */
        $voter = $this->getUser();

        $existing = $roundTable->getThreads()->first();

        if (!($existing instanceof Thread)) {
            $thread = new Thread();
            $thread->setCategory(ThreadCategory::ROUND_TABLE);
            $thread->setTitle('Discussion: '.$roundTable->getTitle());
            $thread->setContent('Open round table discussion – '.$roundTable->getTitle());
            $thread->setRoundTable($roundTable);
            $thread->setAuthor($voter);
            $thread->setAuthorName($voter->getFullName());
            $thread->setSlug('round-table-'.$roundTable->getId().'-'.substr(bin2hex(random_bytes(3)), 0, 6));
            $em->persist($thread);
            $em->flush();
            $existing = $thread;
        }

        return $this->redirectToRoute('app_forum_show', ['slug' => $existing->getSlug()]);
    }
}