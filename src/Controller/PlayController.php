<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\CardsService;
use App\Repository\CardRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;

final class PlayController extends AbstractController
{
    #[Route('/play/{playerName}', name: 'app_play')]
    public function index(Request $request, CardRepository $cardRepository, CardsService $cardsService, GameRepository $gameRepository, string $playerName): Response
    {
        $game = $gameRepository->findOneBy(['player' => $playerName], ['id' => 'DESC']);
        if ($game === null) {
            throw $this->createNotFoundException('Game not found for this player.');
        }

        $session = $request->getSession();
        if ($session !== null) {
            $session->set('current_game_id', $game->getId());
            $session->set('current_player_name', $playerName);
        }

        $cards = $cardsService->getCardByPlayer($cardRepository, 4, $game, null);
        $playableCards = $cardsService->getPlayableCards($cardRepository, 4, $game);
        $playableCardIds = array_map(static fn($card): int => (int) $card->getId(), $playableCards);
        $canPlayerDraw = $game->getCurrentPlayer() === 4 && count($playableCards) === 0;
        $playerNames = [
            1 => 'Zeus',
            2 => 'Anubis',
            3 => 'Odin',
            4 => $playerName . ' (Vous)',
        ];
        $cardCountByPlayer = [];
        for ($i = 1; $i <= 4; $i++) {
            $cardCountByPlayer[$i] = $cardsService->getCountCardByPLayer($cardRepository, $i, $game);
        }

        $winnerId = null;
        foreach ($cardCountByPlayer as $id => $count) {
            if ($count === 0) {
                $winnerId = $id;
                break;
            }
        }

        $isGameOver = $winnerId !== null;
        $cardInit = $cardsService->getLastPLayedCard($cardRepository, $game);

        return $this->render('play/index.html.twig', [
            'controller_name' => 'PlayController',
            'playerName' => $playerName,
            'cards' => $cards,
            'playableCardIds' => $playableCardIds,
            'playerNames' => $playerNames,
            'cardCountByPlayer' => $cardCountByPlayer,
            'cardInit' => $cardInit,
            'currentPlayer' => $game->getCurrentPlayer(),
            'currentPlayerLabel' => $playerNames[$game->getCurrentPlayer()] ?? ('Joueur ' . $game->getCurrentPlayer()),
            'canPlayerDraw' => $canPlayerDraw,
            'isGameOver' => $isGameOver,
            'winnerId' => $winnerId,
            'winnerLabel' => $winnerId !== null ? ($playerNames[$winnerId] ?? ('Joueur ' . $winnerId)) : null,
        ]);
    }

    #[Route('/play/{playerName}/draw', name: 'app_draw', methods: ['POST'])]
    public function draw(string $playerName, GameRepository $gameRepository, CardRepository $cardRepository, CardsService $cardsService, EntityManagerInterface $entityManager): Response
    {
        $game = $gameRepository->findOneBy(['player' => $playerName], ['id' => 'DESC']);
        if ($game === null) {
            throw $this->createNotFoundException('Game not found for this player.');
        }

        for ($i = 1; $i <= 4; $i++) {
            if ($cardsService->getCountCardByPLayer($cardRepository, $i, $game) === 0) {
                return $this->redirectToRoute('app_play', ['playerName' => $playerName]);
            }
        }

        if ($game->getCurrentPlayer() !== 4) {
            return $this->redirectToRoute('app_play', ['playerName' => $playerName]);
        }

        if ($game->getPendingDraw() === 0 && $cardsService->canPlayerPlay($cardRepository, 4, $game)) {
            return $this->redirectToRoute('app_play', ['playerName' => $playerName]);
        }

        $cardsService->drawAndPassTurn($entityManager, $game, 4);

        return $this->redirectToRoute('app_play', ['playerName' => $playerName]);
    }

    #[Route('/enemy', name: 'app_enemy', methods: ['GET'])]
    #[Route('/ennemy', name: 'app_ennemy', methods: ['GET'])]
    public function enemy(Request $request, GameRepository $gameRepository, CardRepository $cardRepository, CardsService $cardsService, EntityManagerInterface $entityManager): Response
    {
        $enemyId = (int) $request->query->get('id', 0);
        $playerName = trim((string) $request->query->get('playerName', ''));

        if ($enemyId < 1 || $enemyId > 3) {
            throw $this->createNotFoundException('Enemy id is invalid.');
        }

        $game = null;
        if ($playerName !== '') {
            $game = $gameRepository->findOneBy(['player' => $playerName], ['id' => 'DESC']);
        }

        if ($game === null) {
            $sessionGameId = $request->getSession()?->get('current_game_id');
            if ($sessionGameId !== null) {
                $game = $gameRepository->find($sessionGameId);
                if ($game !== null) {
                    $playerName = (string) $game->getPlayer();
                }
            }
        }

        if ($game === null) {
            throw $this->createNotFoundException('Game not found for this player.');
        }

        for ($i = 1; $i <= 4; $i++) {
            if ($cardsService->getCountCardByPLayer($cardRepository, $i, $game) === 0) {
                return $this->redirectToRoute('app_play', ['playerName' => $playerName]);
            }
        }

        $cardsService->playEnemyTurn($entityManager, $cardRepository, $game, $enemyId);

        return $this->redirectToRoute('app_play', ['playerName' => $playerName]);
    }
}
