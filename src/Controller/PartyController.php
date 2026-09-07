<?php

namespace App\Controller;

use App\Entity\PoliticalParty;
use App\Repository\PoliticalPartyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PartyController extends AbstractController
{
    #[Route('/parties', name: 'app_parties')]
    public function index(PoliticalPartyRepository $repository): Response
    {
        return $this->render('party/index.html.twig', [
            'parties' => $repository->findByStatus(),
        ]);
    }

    #[Route('/parties/{id}', name: 'app_party_show', requirements: ['id' => '\d+'])]
    public function show(PoliticalParty $party): Response
    {
        return $this->render('party/show.html.twig', [
            'party' => $party,
            'manifesto' => $party->getManifesto(),
        ]);
    }
}
