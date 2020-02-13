<?php

declare(strict_types=1);

namespace App\Service;


use App\Entity\Answer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class AnswerService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getAnswers()
    {
        return $this->em->getRepository(Answer::class)->findAll();
    }

    public function getAnswerById(int $id): ?Answer
    {
        return $this->em->getRepository(Answer::class)->find($id);
    }

    public function addAnswer(Answer $answer): void
    {
        $this->em->persist($answer);
        $this->em->flush();
    }

    public function editAnswer(Answer $answer): void
    {
        $this->em->persist($answer);
        $this->em->flush();
    }

    public function deleteAnswer(Answer $answer): void
    {
        $this->em->remove($answer);
        $this->em->flush();
    }
}