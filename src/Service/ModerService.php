<?php


namespace App\Service;


use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;

class ModerService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function confirmQuiz(Quiz $quiz)
    {
        $quiz->setIsChecked(true);
        $quiz->setIsActive(true);
        $this->em->flush();
    }

    public function denyQuiz(Quiz $quiz)
    {
        $this->em->remove($quiz);

        $user = $quiz->getUser();

        $user->setViolation($user->getViolation() + 1);

        if ($user->getViolation() > 4)
            $user->setIsActive(false);

        $this->em->persist($user);
        $this->em->flush();
    }
}