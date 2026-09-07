<?php

namespace App\Repository;

use App\Entity\Poll;
use App\Entity\PollQuestion;
use App\Entity\PollResponse;
use App\Entity\Voter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PollResponse>
 */
class PollResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PollResponse::class);
    }

    public function findOneByPollAndVoter(Poll $poll, Voter $voter): ?PollResponse
    {
        return $this->findOneBy(['poll' => $poll, 'voter' => $voter]);
    }

    public function countForPoll(Poll $poll): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.poll = :poll')
            ->setParameter('poll', $poll)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<int, array{question: PollQuestion, counts: array<int, int>, total: int}>
     */
    public function surveyResults(Poll $poll): array
    {
        $results = [];
        $responses = $this->findBy(['poll' => $poll]);

        foreach ($poll->getQuestions() as $question) {
            $counts = [];
            $total = 0;
            foreach ($question->getOptions() as $option) {
                $optionCount = 0;
                foreach ($responses as $response) {
                    foreach ($response->getSelections() as $selection) {
                        if ($selection->getOption()->getId() === $option->getId()) {
                            ++$optionCount;
                        }
                    }
                }
                $counts[$option->getId()] = $optionCount;
                $total += $optionCount;
            }
            $results[$question->getId()] = [
                'question' => $question,
                'counts' => $counts,
                'total' => $total,
            ];
        }

        return $results;
    }
}
