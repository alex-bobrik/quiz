<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Quiz;
use App\Entity\QuizCategory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Security;

class QuizService
{
    private $em;

    private $security;

    private $gameService;

    private $fileUploader;

    // Milliseconds (1s = 60000ms)
    const MILLISECONDS = 60000;

    // Average read speed is 1500syms/min
    const READ_SPEED = 1500;

    // Min time limit is 15000ms (15s)
    const MIN_TIME_LIMIT = 15000;

    public function __construct
    (
        EntityManagerInterface $em,
        Security $security,
        GameService $gameService,
        FileUploader $fileUploader
    )
    {
        $this->em = $em;
        $this->security = $security;
        $this->gameService = $gameService;
        $this->fileUploader = $fileUploader;
    }

    public function findById(int $id): ?Quiz
    {
        return $this->em->getRepository(Quiz::class)->find($id);
    }

    public function saveQuiz(Quiz $quiz): void
    {
        $file = new UploadedFile($quiz->getImage(), 'fileName');

        $quiz->setImage($this->fileUploader->uploadQuizImage($file, $quiz));
        $currentUser = $this->security->getUser();

        $user = $this->em->getRepository(User::class)->find($currentUser);

        $date = new \DateTime('now');
        $quiz->setCreated($date);
        $quiz->setIsActive(false);
        $quiz->setIsChecked(false);
        $quiz->setUser($user);

        $this->em->persist($quiz);
        $this->em->flush();
    }

    public function changeStatus(int $id): void
    {
        $quiz = $this->em->getRepository(Quiz::class)->findOneBy(['id' => $id]);

        if ($quiz->getIsActive()) {
            $this->endAllGames($quiz);
            $quiz->setIsActive(false);
        } else {
            $quiz->setIsActive(true);
        }

        $this->em->persist($quiz);
        $this->em->flush();
    }

    private function endAllGames(Quiz $quiz)
    {
        $games = $quiz->getGames();

        foreach ($games as $game) {
            $this->gameService->endGame($game);
        }
    }

    public function saveQuizCategory(QuizCategory $quizCategory)
    {
        $this->em->persist($quizCategory);
        $this->em->flush();
    }

    public function getTimeLimit(Quiz $quiz): int
    {
        $quizQuestions = $quiz->getQuestions();

        $characters = 0;
        foreach ($quizQuestions as $quizQuestion) {
            $characters += strlen($quizQuestion->getQuestion()->getText());
            $answers = $quizQuestion->getQuestion()->getAnswers();
            foreach ($answers as $answer) {
                $characters += strlen($answer->getText());
            }
        }

        $timeLimit = ($characters * self::MILLISECONDS) / self::READ_SPEED;

        if ($timeLimit < self::MIN_TIME_LIMIT) {
            $timeLimit = self::MIN_TIME_LIMIT;
        }

        return $timeLimit;
    }

}