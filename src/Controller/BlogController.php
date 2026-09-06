<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog_list')]
    public function list(Request $request): JsonResponse
    {
        return $this->json([
            'status' => 'OK',
            'ip' => $request->getClientIp(),
        ]);
    }

    #[Route('/blog/{slug}', name: 'blog_show')]
    public function show(Request $request, string $slug): JsonResponse
    {
        return $this->json([
            'status' => 'OK',
            'ip' => $request->getClientIp(),
        ]);
    }

    #[Route('/blog/archive/{year}', requirements: ['year' => '\d{4}'])]
    public function archive(Request $request, int $year): JsonResponse
    {
        return $this->json([
            'status' => 'OK',
            'ip' => $request->getClientIp(),
        ]);
    }
}
