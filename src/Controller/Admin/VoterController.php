<?php

namespace App\Controller\Admin;

use App\Entity\Enum\VoterStatus;
use App\Entity\Voter;
use App\Form\VoterSearchType;
use App\Repository\DistrictRepository;
use App\Repository\VoterRepository;
use App\Service\VoterRegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/voters', name: 'admin_voter_')]
class VoterController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(
        Request $request,
        VoterRepository $voterRepository,
        DistrictRepository $districtRepository,
    ): Response {
        $form = $this->createForm(VoterSearchType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);
        $status = null;
        $term = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $term = $data['term'] ?? null;
            $statusStr = $data['status'] ?? null;
            $status = null !== $statusStr ? VoterStatus::from($statusStr) : null;
        }

        $voters = $voterRepository->search($term, $status, ['createdAt' => 'DESC'], 200);

        return $this->render('admin/voters/index.html.twig', [
            'voters' => $voters,
            'form' => $form,
            'districts' => $districtRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(Voter $voter, DistrictRepository $districtRepository): Response
    {
        return $this->render('admin/voters/show.html.twig', [
            'voter' => $voter,
            'registrations' => $voter->getElectionRegistrations(),
            'districts' => $districtRepository->findAll(),
        ]);
    }

    #[Route('/{id}/set-status/{status}', name: 'set_status', requirements: ['id' => '\d+'])]
    public function setStatus(Voter $voter, string $status, EntityManagerInterface $em): Response
    {
        $voter->setStatus(VoterStatus::from($status));
        $em->flush();
        $this->addFlash('success', sprintf('Voter %s status set to %s.', $voter->getVoterNumber(), $status));

        return $this->redirectToRoute('admin_voter_show', ['id' => $voter->getId()]);
    }

    #[Route('/{id}/set-district', name: 'set_district', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function setDistrict(Voter $voter, Request $request, EntityManagerInterface $em, DistrictRepository $districtRepository): Response
    {
        $districtId = $request->request->get('district_id');
        $district = $districtId ? $districtRepository->find((int) $districtId) : null;
        $voter->setDistrict($district);
        $em->flush();
        $this->addFlash('success', 'Voter district updated.');

        return $this->redirectToRoute('admin_voter_show', ['id' => $voter->getId()]);
    }

    #[Route('/import', name: 'import', methods: ['POST'])]
    public function import(
        Request $request,
        DistrictRepository $districtRepository,
        EntityManagerInterface $em,
        VoterRegistrationService $registrationService,
    ): Response {
        $csvFile = $request->files->get('csv_file');
        if (null === $csvFile) {
            $this->addFlash('error', 'Please upload a CSV file.');

            return $this->redirectToRoute('admin_voter_index');
        }

        $districtId = $request->request->get('district_id');
        $district = $districtId ? $districtRepository->find((int) $districtId) : null;

        $csvContent = file_get_contents((string) $csvFile->getPathname());
        $importer = new \App\Service\CsvImportService($em, $registrationService);
        $result = $importer->importVoters($csvContent, $district);

        if ($result['imported'] > 0) {
            $this->addFlash('success', sprintf('Imported %d voters.', $result['imported']));
        }
        if ($result['skipped'] > 0) {
            foreach ($result['errors'] as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->redirectToRoute('admin_voter_index');
    }

    #[Route('/export.csv', name: 'export')]
    public function export(
        VoterRepository $voterRepository,
    ): Response {
        $rows = $voterRepository->findBy([], ['createdAt' => 'ASC']);
        $csv = fopen('php://output', 'w');
        fputcsv($csv, ['voter_number', 'national_id', 'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth', 'email', 'phone', 'city', 'district', 'status', 'confirmation_code', 'registered']);

        foreach ($rows as $voter) {
            fputcsv($csv, [
                $voter->getVoterNumber(),
                $voter->getNationalId(),
                $voter->getFirstName(),
                $voter->getMiddleName(),
                $voter->getLastName(),
                $voter->getGender(),
                $voter->getDateOfBirth()?->format('Y-m-d'),
                $voter->getEmail(),
                $voter->getPhone(),
                $voter->getCity(),
                $voter->getDistrict()?->getName(),
                $voter->getStatus()->value,
                $voter->getConfirmationCode(),
                $voter->getConfirmedAt()?->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($csv);

        return new Response(null, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="voters_'.date('Y-m-d').'.csv"',
        ]);
    }
}
