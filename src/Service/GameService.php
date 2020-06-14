<?php declare(strict_types=1);


namespace App\Service;


use App\Entity\Answer;
use App\Entity\Game;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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
        /** @var Game $game */
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
        $this->em->getRepository(Game::class)
            ->createQueryBuilder('g')
            ->update()
            ->set('g.result_score', $game->getResultScore() + 1)
            ->where('g.id = :id')
            ->setParameter('id', $game->getId())
            ->getQuery()
            ->execute();
    }

    public function getLeaders(Quiz $quiz): ?array
    {
        return $this->em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->join('u.games',  'g')
            ->where('g.quiz = :quiz')
            ->andWhere('g.end_date is not null')
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
        $this->em->getRepository(Game::class)
            ->createQueryBuilder('g')
            ->update()
            ->set('g.QuestionNumber', $game->getQuestionNumber() + 1)
            ->where('g.id = :id')
            ->setParameter('id', $game->getId())
            ->getQuery()
            ->execute();
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
        $date = new \DateTime('now');
        $dateStart = $game->getStartDate();
        $dateDiff = $date->getTimestamp() - $dateStart->getTimestamp();

        $this->em->getRepository(Game::class)
            ->createQueryBuilder('g')
            ->update()
            ->set('g.end_date', '?1')
            ->set('g.result_time', '?2')
            ->where('g.id = :id')
            ->setParameter('id', $game->getId())
            ->setParameter(1, $date)
            ->setParameter(2, $dateDiff)
            ->getQuery()
            ->execute();
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

    public function getCorrectArray(Question $question): array
    {
        $arr = array();

        foreach ($question->getAnswers() as $answer) {
            if ($answer->getIsCorrect())
            {
                $arr[] = $answer->getId();
            }
        }

        return $arr;
    }

}