<?php

namespace App\Controller\Admin;

use App\Entity\ObservationReport;
use App\Repository\ObservationReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reports', name: 'admin_report_')]
class ReportController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, ObservationReportRepository $reportRepository): Response
    {
        $onlyIrregular = (bool) $request->query->get('irregular');

        $reports = $onlyIrregular
            ? $reportRepository->findRecentWithIrregularities(100)
            : $reportRepository->findBy([], ['submittedAt' => 'DESC'], 100);

        return $this->render('admin/reports/index.html.twig', [
            'reports' => $reports,
            'onlyIrregular' => $onlyIrregular,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(ObservationReport $report): Response
    {
        return $this->render('admin/reports/show.html.twig', ['report' => $report]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(ObservationReport $report, EntityManagerInterface $em): Response
    {
        $em->remove($report);
        $em->flush();
        $this->addFlash('success', 'Report deleted.');

        return $this->redirectToRoute('admin_report_index');
    }
}