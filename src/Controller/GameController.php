<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Form\AnswerType;
use App\Form\QuestionType;
use App\Service\GameService;
use App\Service\QuestionService;
use App\Service\QuizService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Validator\Constraints\Json;

class GameController extends AbstractController
{
    /**
     * @Route("/games", name="games_show")
     * @param EntityManagerInterface $em
     * @param GameService $gameService
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function showGames(EntityManagerInterface $em, GameService $gameService, PaginatorInterface $paginator, Request $request): Response
    {
        $quizesRepository = $this->getDoctrine()->getRepository(Quiz::class);

        $quizesQuery = $quizesRepository->createQueryBuilder('q')
            ->getQuery();

        $quizes = $paginator->paginate(
            $quizesQuery,
            $request->query->getInt('page', 1),
            3
        );

        return $this->render('game/index.html.twig', [
            'controller_name' => 'GameController',
            'quizes' => $quizes,
        ]);
    }

    /**
     * @Route("/games/start/{quizId}", name="games_start_play")
     * @param QuizService $quizService
     * @param GameService $gameService
     * @param int $quizId
     * @return Response
     */
    public function startGame(QuizService $quizService, GameService $gameService, int $quizId): Response
    {
        $quiz = $quizService->findById($quizId);

        if (!$quiz->getIsActive()) {
            throw new NotFoundHttpException('Quiz is inactive');
        }

        $game = $gameService->findByQuizUser($quiz, $this->getUser());

        if (!$game)
        {
            $game = $gameService->startGame($quiz);
        }

        if (!$game->getGameIsOver())
        {
            return $this->redirectToRoute('game_play', [
                'gameId' => $game->getId(),
            ]);

        }else
        {
            return $this->redirectToRoute('games_leaders', ['quizId' => $quiz->getId()]);
        }

    }

    /**
     * @Route("/games/play/{gameId}", name="game_play")
     * @param GameService $gameService
     * @param Request $request
     * @param int $gameId
     * @return Response
     */
    public function playGame(GameService $gameService, Request $request, int $gameId): Response
    {
        $game = $gameService->getGameById($gameId);

        if (!$gameService->checkUserPermission($game, $this->getUser()))
        {
            throw $this->createNotFoundException();
        }

        if (!$gameService->checkGameAccess($game))
        {
            throw new NotFoundHttpException('Quiz is inactive');
        }

        if ($game->getGameIsOver())
        {
            return $this->redirectToRoute('games_leaders', ['quizId' => $game->getQuiz()->getId()]);
        }

        $question = $gameService->getCurrentQuestion($game);
        $form = $this->createForm(QuestionType::class, $question);

        $correctAnswer = $gameService->getCorrectAnswer($question);

        $form->handleRequest($request);
        if($form->isSubmitted()) {

            $userQuestion = $form->getData();
            $userAnswer = $gameService->getCorrectAnswer($userQuestion);

            if ($userAnswer === $correctAnswer)
            {
                $gameService->addPoint($game);
                $response = 'CORRECT';
            }else
            {
                $response = 'incorrect';
            }

            if (!($gameService->getQuestionsAmount($game) === $gameService->getQuestionNumber($game)))
                $gameService->setNextQuestion($game);
            else
                $gameService->endGame($game);

            return new JsonResponse($response);
        }

        return $this->render('game/game_play.html.twig', [
            'controller_name' => 'GameController',
            'game' => $game,
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/games/leaders/{quizId}", name="games_leaders")
     * @param QuizService $quizService
     * @param GameService $gameService
     * @param int $quizId
     * @return Response
     */
    public function gameLeaders(QuizService $quizService, GameService $gameService, int $quizId): Response
    {
        $quiz = $quizService->findById($quizId);

        if (!$quiz) {
            throw $this->createNotFoundException('Quiz is not found');
        }

        $leaders = $gameService->getLeaders($quiz);
        $userPosition = $gameService->getUserLeaderboardPosition($leaders);

        $game = $gameService->findByQuizUser($quiz, $this->getUser());

        if (!$game)
        {
            $isPassed = false;
        }else
        {
            if (!$game->getGameIsOver())
            {
                return $this->redirectToRoute('game_play', ['gameId' => $game->getId()]);
            }

            $isPassed = true;
        }

        return $this->render('game/game_leaders.html.twig', [
            'quizId' => $quiz->getId(),
            'isPassed' => $isPassed,
            'leaders' => $leaders,
            'userPos' => $userPosition,
            'game' => $game,
        ]);

    }
}
