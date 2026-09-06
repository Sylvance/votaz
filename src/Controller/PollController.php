<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\PollOption;
use App\Entity\PollResponse;
use App\Entity\PollSelection;
use App\Repository\PollRepository;
use App\Repository\PollResponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PollController extends AbstractController
{
    #[Route('/polls', name: 'app_polls')]
    public function index(PollRepository $pollRepository): Response
    {
        return $this->render('poll/index.html.twig', [
            'openPolls' => $pollRepository->findOpenAndPast(),
            'pastPolls' => $pollRepository->findPast(),
        ]);
    }

    #[Route('/polls/{id}', name: 'app_poll_show', requirements: ['id' => '\d+'])]
    public function show(
        Poll $poll,
        PollResponseRepository $responseRepository,
    ): Response {
        $response = null;
        if ($this->isGranted('ROLE_VOTER')) {
            $response = $responseRepository->findOneByPollAndVoter($poll, $this->getUser());
        }

        $results = $responseRepository->surveyResults($poll);

        return $this->render('poll/show.html.twig', [
            'poll' => $poll,
            'response' => $response,
            'results' => $results,
            'userVoter' => $this->getUser(),
        ]);
    }

    #[Route('/polls/{id}/respond', name: 'app_poll_respond', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function respond(Poll $poll, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VOTER');
        if ($poll->requiresAuth()) {
            $this->denyAccessUnlessGranted('ROLE_VOTER');
        }
        if (!$poll->isOpen()) {
            $this->addFlash('error', 'This poll is not open for responses.');
        }

        $voter = $this->getUser();
        $existing = $em->getRepository(PollResponse::class)->findOneBy(['poll' => $poll, 'voter' => $voter]);
        if (null !== $existing) {
            $this->addFlash('error', 'You have already responded to this poll.');
        } else {
            $response = new PollResponse();
            $response->setPoll($poll);
            $response->setVoter($voter);

            $questions = $poll->getQuestions();
            $isValid = true;

            foreach ($questions as $question) {
                $selected = $request->request->all('question_'.$question->getId());

                if ($question->isRequired() && empty($selected)) {
                    $isValid = false;
                    break;
                }

                if (is_array($selected)) {
                    $selected = array_map('intval', $selected);
                    foreach ($selected as $optionId) {
                        $option = $em->getRepository(PollOption::class)->find($optionId);
                        if (null === $option || $option->getQuestion()->getId() !== $question->getId()) {
                            $isValid = false;
                            break 2;
                        }
                        $selection = new PollSelection();
                        $selection->setResponse($response);
                        $selection->setQuestion($question);
                        $selection->setOption($option);
                        $response->addSelection($selection);
                    }
                }
            }

            if ($isValid) {
                $em->persist($response);
                $em->flush();
                $this->addFlash('success', 'Your response has been recorded. Thank you for your input.');
            } else {
                $this->addFlash('error', 'Please answer all required questions before submitting.');
            }
        }

        return $this->redirectToRoute('app_poll_show', ['id' => $poll->getId()]);
    }
}