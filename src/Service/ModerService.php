<?php


namespace App\Service;


use App\Entity\Quiz;
use App\Entity\Violation;
use App\Entity\ViolationAct;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\DateTime;

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

    public function denyQuiz(Quiz $quiz, Violation $violation)
    {
        $this->em->remove($quiz);
        $user = $quiz->getUser();

        $violationAct = new ViolationAct();
        $violationAct->setViolation($violation);
        $violationAct->setUser($user);
        $violationAct->setViolationDate(new \DateTime('now'));

        $user->addViolationAct($violationAct);

        if ($user->getViolationActs()->count() > 4) {
            $user->setIsActive(false);
        }

        $this->em->flush();
    }
}