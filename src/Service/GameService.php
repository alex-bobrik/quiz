<?php declare(strict_types=1);


namespace App\Service;


use App\Entity\Answer;
use App\Entity\Game;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\QuizQuestion;
use App\Entity\User;
use App\Form\AnswerType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

class GameService
{
    private $em;

    private $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    public function getGameById(int $id): ?Game
    {
        return $this->em->getRepository(Game::class)->find($id);
    }

    public function findByQuizUser(Quiz $quiz, UserInterface $user): ?Game
    {
        $game = $this->em->getRepository(Game::class)
            ->findOneBy(['user' => $user, 'quiz' => $quiz]);

        if ($game)
            return $game;
        return null;
    }

    public function startGame(Quiz $quiz): Game
    {
        $game = new Game();
        $user = $this->security->getUser();
        $currentDate = new \DateTime('now');

        $game->setGameIsOver(false);
        $game->setQuestionNumber(1);
        $game->setResultScore(0);
        $game->setUser($user);
        $game->setQuiz($quiz);
        $game->setStartDate($currentDate);

        $this->em->persist($game);
        $this->em->flush();

        return $game;
    }

    public function addPoint(Game $game): void
    {
        $game->setResultScore($game->getResultScore() + 1);
        $this->em->persist($game);
        $this->em->flush();
    }

    public function getLeaders(Quiz $quiz): ?array
    {
        return $this->em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->join('u.games',  'g')
            ->where('g.quiz = :quiz')
            ->andWhere('g.gameIsOver = 1')
            ->orderBy('g.result_score', 'DESC')
            ->addOrderBy('g.result_time', 'ASC')
            ->setParameter('quiz', $quiz)
            ->getQuery()
            ->getResult();
    }

    public function getUserLeaderboardPosition(array $users): ?int
    {
        $position = 0;
        $targetUser = $this->security->getUser();
        foreach ($users as $user)
        {
            $position++;
            if ($user == $targetUser)
            {
                return $position;
            }
        }

        return null;
    }

    public function getQuestionsAmount(Game $game): int
    {
        return $game->getQuiz()->getQuestions()->count();
    }

    public function setNextQuestion(Game $game): void
    {
        $game->setQuestionNumber($game->getQuestionNumber() + 1);

        $this->em->persist($game);
        $this->em->flush();
    }

    public function getQuestionNumber(Game $game): ?int
    {
        return $game->getQuestionNumber();
    }

    public function getCurrentQuestion(Game $game): ?Question
    {
        $questions = $game->getQuiz()->getQuestions();

        return $questions[$game->getQuestionNumber() - 1]->getQuestion();
    }

    public function endGame(Game $game): void
    {
        $game->setGameIsOver(true);

        $currentDate = new \DateTime('now');
        $game->setEndDate($currentDate);

        $dateStart = $game->getStartDate();
        $dateEnd = $game->getEndDate();
        $dateDiff = $dateEnd->getTimestamp() - $dateStart->getTimestamp();
        $game->setResultTime($dateDiff);

        $this->em->persist($game);
        $this->em->flush();
    }

    public function getCorrectAnswer(Question $question): ?Answer
    {
        foreach ($question->getAnswers() as $answer)
        {
            if ($answer->getIsCorrect())
            {
                return $answer;
            }
        }

        return null;
    }

    public function checkUserPermission(Game $game, UserInterface $user): bool
    {
        $targetUser = $this
            ->em
            ->getRepository(User::class)
            ->find($game->getUser());

        if ($user->getUsername() != $targetUser->getUsername()) {
            return false;
        }

        return true;
    }

    public function checkGameAccess(Game $game): bool
    {
        $quiz = $game->getQuiz();

        if (!$quiz->getIsActive()) {
            return false;
        }

        return true;
    }
}