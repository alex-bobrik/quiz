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

    private $mailSender;

    public function __construct(EntityManagerInterface $em, MailSender $mailSender)
    {
        $this->em = $em;
        $this->mailSender = $mailSender;
    }

    public function confirmQuiz(Quiz $quiz)
    {
        $quiz->setIsChecked(true);
        $quiz->setIsActive(true);
        $this->em->flush();
    }

    public function denyQuiz(Quiz $quiz, Violation $violation)
    {
        // TODO: Send email
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

        $message = 'Обнаржуено нарушение в викторине ' .$quiz->getName() . ' по причине '.$violation->getName() .', 
            викторина будет удалена. Дальнейшие нарушения приведут к блокировке аккаунта.';

        $this->mailSender->send('Нарушение', $user->getEmail(), $message);

        $this->em->flush();
    }
}