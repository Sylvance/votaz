<?php
namespace App\Controller;

use App\Dto\SignupRequest;
use App\Form\SignupType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SignupController extends AbstractController
{
    #[Route('/signup', methods: ['GET', 'POST'])]
    public function signup(Request $request): Response
    {
        $signup = new SignupRequest();
        $form = $this->createForm(SignupType::class, $signup);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $signup now contains the submitted data
            // ... do something with it (save it, send an email, ...)
            return $this->redirectToRoute('signup_success');
        }
        return $this->render('signup.html.twig', [
            'signup_form' => $form,
        ]);
    }
}
