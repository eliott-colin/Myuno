<?php 

namespace App\Services;

use App\Repository\GameRepository;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;

class GameService {
    public function initGame(string $playerName, GameRepository $gameRepository , EntityManagerInterface $entityManager): Game | false
    {
        $gameExist = $gameRepository->findBy(['player' => $playerName]);
        if ($gameExist === []) {
            return false;
        }else {
            $game = new Game();
            $game->setPlayer($playerName);
            $entityManager->persist($game);
            $entityManager->flush();
            return $game;   
        }
    }
    public function getIdGameByPlayerName(string $playerName, GameRepository $gameRepository): Game | null
    {    
        $game = $gameRepository->findOneBy(['player' => $playerName]);
        return $game;
    }   
}