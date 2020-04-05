<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Rating;
use App\Entity\User;
use App\Form\Question\QuestionType;
use App\Service\GameService;
use App\Service\QuizService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    /**
     * @Route("/games", name="games_show")
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function showGames(PaginatorInterface $paginator, Request $request): Response
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
     * @Route("/games/start/{quizId}", name="games_start_play", requirements={"quizId"="\d+"})
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
            return $this->redirectToRoute('games_details', ['quizId' => $quiz->getId()]);
        }

    }

    /**
     * @Route("/games/play/{gameId}", name="game_play", requirements={"quizId"="\d+"})
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
            return $this->redirectToRoute('games_details', ['quizId' => $game->getQuiz()->getId()]);
        }

        $question = $gameService->getCurrentQuestion($game);
        $form = $this->createForm(QuestionType::class, $question);

        $currentArray = $gameService->getCorrectArray($question);

        $form->handleRequest($request);
        if($form->isSubmitted()) {

            $userQuestion = $form->getData();
            $userArray = $gameService->getCorrectArray($userQuestion);

            if ($currentArray === $userArray)
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
     * @Route("/games/details/{quizId}", name="games_details", requirements={"quizId"="\d+"})
     * @param QuizService $quizService
     * @param GameService $gameService
     * @param int $quizId
     * @return Response
     */
    public function gameLeaders(QuizService $quizService, GameService $gameService, int $quizId): Response
    {
        $rating = new Rating();
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
            $user = $this->getDoctrine()->getRepository(User::class)->find($game->getUser());
            // Check is user rated this quiz and set stars amount
            $rating = $this->getDoctrine()->getRepository(Rating::class)->findOneBy(['user' => $user, 'quiz' => $quiz]);


            if (!$rating) {
                $rating = new Rating();
                $rating->setStars(0);
            }
        }

        return $this->render('game/game_details.html.twig', [
            'quizId' => $quiz->getId(),
            'isPassed' => $isPassed,
            'leaders' => $leaders,
            'userPos' => $userPosition,
            'game' => $game,
            'rating' => $rating,
        ]);
    }

    /**
     * @Route("games/{quizId}/rating/{stars}", name="games_rate", methods={"POST"})
     * @param int $stars
     * @param int $quizId
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function rateGame(int $stars, int $quizId, Request $request, EntityManagerInterface $em)
    {
        $rating = new Rating();

        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
        $quiz = $this->getDoctrine()->getRepository(Quiz::class)->find($quizId);

        $rating = $this->getDoctrine()->getRepository(Rating::class)->findOneBy(['quiz' => $quiz, 'user' => $user]);

        if (!$rating) {
            $rating = new Rating();
        }

        $rating->setUser($user);
        $rating->setQuiz($quiz);
        $rating->setStars($stars);

        $em->persist($rating);
        $em->flush();

        return new JsonResponse();
    }
}
