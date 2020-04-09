<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Quiz;
use App\Entity\QuizCategory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class QuizService
{
    private $em;

    private $security;

    private $gameService;

    public function __construct(EntityManagerInterface $em, Security $security, GameService $gameService)
    {
        $this->em = $em;
        $this->security = $security;
        $this->gameService = $gameService;
    }

    public function findById(int $id): ?Quiz
    {
        return $this->em->getRepository(Quiz::class)->find($id);
    }

    public function saveQuiz(Quiz $quiz): void
    {
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

}