<?php

namespace App\Controller\Admin;

use App\Entity\RoundTable;
use App\Form\RoundTableType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/round-tables', name: 'admin_roundtable_')]
class RoundTableController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('admin/round_tables/index.html.twig', [
            'roundTables' => $em->getRepository(RoundTable::class)->findBy([], ['scheduledAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $roundTable = new RoundTable();
        $form = $this->createForm(RoundTableType::class, $roundTable);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($roundTable);
            $em->flush();
            $this->addFlash('success', 'Round table scheduled.');

            return $this->redirectToRoute('admin_roundtable_show', ['id' => $roundTable->getId()]);
        }

        return $this->render('admin/round_tables/form.html.twig', ['form' => $form, 'roundTable' => $roundTable]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(RoundTable $roundTable): Response
    {
        return $this->render('admin/round_tables/show.html.twig', ['roundTable' => $roundTable]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(RoundTable $roundTable, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(RoundTableType::class, $roundTable);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Round table updated.');

            return $this->redirectToRoute('admin_roundtable_show', ['id' => $roundTable->getId()]);
        }

        return $this->render('admin/round_tables/form.html.twig', ['form' => $form, 'roundTable' => $roundTable]);
    }
}