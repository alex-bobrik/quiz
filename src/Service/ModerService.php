<?php


namespace App\Service;


use App\Entity\Quiz;
use App\Entity\User;
use App\Entity\Violation;
use App\Entity\ViolationAct;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class ModerService
{
    private $em;

    private $mailSender;

    private $fileUploader;

    public function __construct(EntityManagerInterface $em, MailSender $mailSender, FileUploader $fileUploader)
    {
        $this->em = $em;
        $this->mailSender = $mailSender;
        $this->fileUploader = $fileUploader;
    }

    public function confirmQuiz(Quiz $quiz, User $whoConfirm)
    {
        $quiz->setIsChecked(true);
        $quiz->setIsActive(true);
        $quiz->setCheckedBy($whoConfirm);
        $this->em->flush();
    }

    public function denyQuiz(Quiz $quiz, Violation $violation)
    {
        $this->em->remove($quiz);

        $this->fileUploader->removeImage(
            $this->fileUploader->getQuizesDirectory(),
            $quiz->getImage()
        );

        $user = $quiz->getUser();

        $this->removeUnckeckedQuizes($user);

        $violationAct = new ViolationAct();
        $violationAct->setViolation($violation);
        $violationAct->setUser($user);
        $violationAct->setViolationDate(new \DateTime('now'));

        $user->addViolationAct($violationAct);

        $message = 'Обнаржуено нарушение в викторине ' .$quiz->getName() . ' по причине '.$violation->getName() .', 
            викторина будет удалена. Дальнейшие нарушения приведут к блокировке аккаунта.';

        $this->mailSender->send('Нарушение', $user->getEmail(), $message);

        if ($user->getViolationActs()->count() > 4) {
            $user->setIsActive(false);

            $message = 'Вы были заблокированы на сервисе quiz.work за рецидив нарушений.';
            $this->mailSender->send('Блокировка', $user->getEmail(), $message);
        }

        $this->em->flush();
    }

    private function removeUnckeckedQuizes(User $user)
    {
        $uncheckedQuizes = $this->em->getRepository(Quiz::class)
            ->findBy(['user' => $user, 'isChecked' => false]);

        foreach ($uncheckedQuizes as $quiz) {
            $this->em->remove($quiz);
        }
    }
}