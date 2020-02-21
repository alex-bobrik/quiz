<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Quiz;
use App\Entity\User;
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

    public function findById(int $id): ?Quiz
    {
        return $this->em->getRepository(Quiz::class)->find($id);
    }

    public function saveQuiz(Quiz $quiz): void
    {
        $date = new \DateTime('now');
        $quiz->setCreated($date);
        $quiz->setIsActive(true);

        $this->em->persist($quiz);
        $this->em->flush();
    }

    public function changeStatus(int $id): void
    {
        $quiz = $this->em->getRepository(Quiz::class)->findOneBy(['id' => $id]);

        if ($quiz->getIsActive())
        {
            $quiz->setIsActive(false);
        }else
        {
            $quiz->setIsActive(true);
        }

        $this->em->persist($quiz);
        $this->em->flush();
    }

}