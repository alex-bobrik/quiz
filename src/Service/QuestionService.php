<?php declare(strict_types=1);


namespace App\Service;


use App\Entity\Question;
use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;

class QuestionService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function saveQuestion(Question $question): void
    {
        $this->em->persist($question);
        $this->em->flush();
    }

    public function getQuestionById($questionId) : ?Question
    {
        return $this->em->getRepository(Question::class)->find($questionId);
    }
}