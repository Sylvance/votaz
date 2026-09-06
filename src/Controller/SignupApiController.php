<?php
namespace App\Controller;

use App\Dto\SignupRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class SignupApiController extends AbstractController
{
    #[Route('/api/signup', methods: ['POST'])]
    public function signup(
        #[MapRequestPayload] SignupRequest $request,
    ): JsonResponse {
        // if you get here, the JSON payload was valid; otherwise,
        // Symfony already returned a 422 response with the errors
        return $this->json(['welcome' => $request->email]);
    }
}
