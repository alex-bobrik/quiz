<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Quiz;
use App\Service\GameService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    /**
     * @Route("/games", name="games_show")
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function showGames(EntityManagerInterface $em): Response
    {
        $quizes = $em->getRepository(Quiz::class)->findAll();

        return $this->render('game/index.html.twig', [
            'controller_name' => 'GameController',
            'quizes' => $quizes,
        ]);
    }

    /**
     * @Route("/games/{id}", name="game_show_info")
     * @param GameService $gameService
     * @param int $id
     * @return Response
     */
    public function showGameInfo(EntityManagerInterface $em, GameService $gameService, int $id): Response
    {
//        $game = $gameService->getGameById($id);
        $game = $em->getRepository(Quiz::class)->find($id);

        return $this->render('game/game_info.html.twig', [
            'controller_name' => 'GameController',
            'game' => $game,
        ]);
    }

    /**
     * @Route("/games/{id}/play", name="games_play")
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function playGame(EntityManagerInterface $em): Response
    {
        $quizes = $em->getRepository(Quiz::class)->findAll();

        return $this->render('game/index.html.twig', [
            'controller_name' => 'GameController',
            'quizes' => $quizes,
        ]);
    }
}
