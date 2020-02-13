<?php declare(strict_types=1);


namespace App\Service;


use App\Entity\Question;
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
}