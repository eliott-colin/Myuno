<?php 

namespace App\Services;

use App\Repository\GameRepository;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;

class GameService {
    public function initGame(string $playerName, EntityManagerInterface $entityManager): Game
    {
        $game = new Game();
        $game->setPlayer($playerName);
        $game->setCurrentPlayer(4);
        $game->setDirection(1);
        $game->setPendingDraw(0);

        $entityManager->persist($game);
        $entityManager->flush();

        return $game;
    }
    public function getIdGameByPlayerName(string $playerName, GameRepository $gameRepository): Game | null
    {    
        $game = $gameRepository->findOneBy(['player' => $playerName], ['id' => 'DESC']);
        return $game;
    }   
}