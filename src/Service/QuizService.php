<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Quiz;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class QuizService
{
    private $em;

    private $quiz;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createQuiz(Quiz $quiz): void
    {
        $date = new \DateTime('now');
        $quiz->setCreated($date);
        $quiz->setIsActive(true);

        $this->em->persist($quiz);
        $this->em->flush();
    }

}