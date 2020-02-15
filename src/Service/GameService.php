<?php declare(strict_types=1);


namespace App\Service;


use App\Entity\Game;
use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getGameById(int $id): ?Game
    {
        return $this->em->getRepository(Game::class)->find($id);
    }

    public function startGame()
    {

    }

    public function endGame()
    {

    }
}