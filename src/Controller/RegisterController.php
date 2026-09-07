<?php

namespace App\Controller;

use App\Entity\Voter;
use App\Form\RegistrationConfirmationType;
use App\Form\VoterRegistrationType;
use App\Repository\VoterRepository;
use App\Service\VoterRegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        VoterRegistrationService $registrationService,
        VoterRepository $voterRepository,
        EntityManagerInterface $em,
    ): Response {
        $voter = new Voter();
        $form = $this->createForm(VoterRegistrationType::class, $voter, [
            'validation_groups' => ['registration'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = [];
            if (null === $voter->getEmail()) {
                $errors[] = 'An email address is required to receive the confirmation code.';
            }
            if (null !== $voterRepository->findOneByNationalId($voter->getNationalId() ?? '')) {
                $errors[] = 'A voter with this national ID is already registered.';
            }

            if ([] !== $errors) {
                return $this->render('register/register.html.twig', [
                    'form' => $form,
                    'errors' => $errors,
                ]);
            }

            $voter = $registrationService->createVoter($voter);
            $session = $request->getSession();
            $session->set('registration_voter_id', $voter->getId());

            if (null !== $voter->getEmail()) {
                [$token, $code] = $registrationService->sendConfirmationOtp($voter);
                $session->set('otp_dev_code', $code);
            }

            return $this->redirectToRoute('app_register_confirm');
        }

        return $this->render('register/register.html.twig', [
            'form' => $form,
            'errors' => [],
        ]);
    }

    #[Route('/register/confirm', name: 'app_register_confirm', methods: ['GET', 'POST'])]
    public function confirm(
        Request $request,
        VoterRegistrationService $registrationService,
        EntityManagerInterface $em,
    ): Response {
        $session = $request->getSession();
        $voterId = $session->get('registration_voter_id');
        if (null === $voterId) {
            return $this->redirectToRoute('app_register');
        }

        $voter = $em->getRepository(Voter::class)->find((int) $voterId);
        if (null === $voter) {
            $session->clear();

            return $this->redirectToRoute('app_register');
        }

        if ($voter->isConfirmed()) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(RegistrationConfirmationType::class);
        $form->handleRequest($request);
        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['password'] !== $data['confirmPassword']) {
                $error = 'The passwords do not match.';
            } else {
                $ok = $registrationService->confirmVoter($voter, $data['code'], $data['password']);
                if ($ok) {
                    $session->set('registration_done', true);

                    return $this->redirectToRoute('app_register_success');
                }
                $error = 'The confirmation code is invalid or has expired. Please try again or request a new code.';
            }
        }

        return $this->render('register/confirm.html.twig', [
            'form' => $form,
            'voter' => $voter,
            'error' => $error,
            'devCode' => $session->get('otp_dev_code'),
        ]);
    }

    #[Route('/register/resend', name: 'app_register_resend', methods: ['POST'])]
    public function resend(Request $request, VoterRegistrationService $registrationService, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $voterId = $session->get('registration_voter_id');
        if (null === $voterId) {
            return $this->redirectToRoute('app_register');
        }

        $voter = $em->getRepository(Voter::class)->find((int) $voterId);
        if (null === $voter || $voter->isConfirmed()) {
            return $this->redirectToRoute('app_register_confirm');
        }

        [$token, $code] = $registrationService->sendConfirmationOtp($voter);
        $session->set('otp_dev_code', $code);
        $this->addFlash('success', 'A new confirmation code has been sent.');

        return $this->redirectToRoute('app_register_confirm');
    }

    #[Route('/register/success', name: 'app_register_success')]
    public function success(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        if (!$session->get('registration_done')) {
            return $this->redirectToRoute('app_register');
        }

        $voterId = $session->get('registration_voter_id');
        $voter = $voterId ? $em->getRepository(Voter::class)->find((int) $voterId) : null;

        return $this->render('register/success.html.twig', ['voter' => $voter]);
    }
}
