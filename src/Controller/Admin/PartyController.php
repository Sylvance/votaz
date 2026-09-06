<?php

namespace App\Controller\Admin;

use App\Entity\Manifesto;
use App\Entity\PoliticalParty;
use App\Entity\Enum\PartyStatus;
use App\Form\ManifestoType;
use App\Form\PartyType;
use App\Service\CodeGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/parties', name: 'admin_party_')]
class PartyController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('admin/parties/index.html.twig', [
            'parties' => $em->getRepository(PoliticalParty::class)->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $party = new PoliticalParty();
        $party->setRegistrationNumber(CodeGenerator::partyRegistrationNumber());
        $form = $this->createForm(PartyType::class, $party);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($party);
            $em->flush();
            $this->addFlash('success', 'Political party registered.');

            return $this->redirectToRoute('admin_party_show', ['id' => $party->getId()]);
        }

        return $this->render('admin/parties/form.html.twig', ['form' => $form, 'party' => $party]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(PoliticalParty $party): Response
    {
        return $this->render('admin/parties/show.html.twig', ['party' => $party]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(PoliticalParty $party, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PartyType::class, $party);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (PartyStatus::APPROVED === $party->getStatus() && null === $party->getRegisteredAt()) {
                $party->setRegisteredAt(new \DateTimeImmutable());
            }
            $em->flush();
            $this->addFlash('success', 'Party updated.');

            return $this->redirectToRoute('admin_party_show', ['id' => $party->getId()]);
        }

        return $this->render('admin/parties/form.html.twig', ['form' => $form, 'party' => $party]);
    }

    #[Route('/{id}/announce-leader', name: 'announce_leader', requirements: ['id' => '\d+'])]
    public function announceLeader(PoliticalParty $party, EntityManagerInterface $em): Response
    {
        $party->setLeaderAnnouncedAt(new \DateTimeImmutable());
        $em->flush();
        $this->addFlash('success', sprintf('Leadership announced for %s.', $party->getName()));

        return $this->redirectToRoute('admin_party_show', ['id' => $party->getId()]);
    }

    #[Route('/{id}/manifesto/edit', name: 'manifesto_edit', requirements: ['id' => '\d+'])]
    public function editManifesto(PoliticalParty $party, Request $request, EntityManagerInterface $em): Response
    {
        $manifesto = $party->getManifesto();
        if (null === $manifesto) {
            $manifesto = new Manifesto();
            $manifesto->setParty($party);
            $manifesto->setTitle(sprintf('%s Manifesto', $party->getName()));
            $em->persist($manifesto);
        }

        $form = $this->createForm(ManifestoType::class, $manifesto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($manifesto->isPublished()) {
                $manifesto->setPublishedAt(new \DateTimeImmutable());
            }
            foreach ($form->get('sections')->getData() as $i => $section) {
                if (null === $section->getId()) {
                    $manifesto->addSection($section);
                }
            }
            $em->flush();
            $this->addFlash('success', 'Manifesto saved.');

            return $this->redirectToRoute('admin_party_show', ['id' => $party->getId()]);
        }

        return $this->render('admin/parties/manifesto.html.twig', [
            'form' => $form,
            'party' => $party,
        ]);
    }
}