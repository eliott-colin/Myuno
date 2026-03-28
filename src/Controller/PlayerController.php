<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\CardsService;
use App\Repository\CardRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;

final class PlayerController extends AbstractController
{
    #[Route('/player', name: 'app_player', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, CardsService $cardService, CardRepository $cardRepository, GameRepository $gameRepository): Response
    {
        $id = (int) ($request->request->get('id', $request->query->get('id', 0)));
        $playerName = trim((string) ($request->request->get('playerName', $request->query->get('playerName', ''))));

        if ($id <= 0) {
            throw $this->createNotFoundException('Invalid player request.');
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

        if ($game->getCurrentPlayer() !== 4) {
            return $this->redirectToRoute('app_play', ['playerName' => $playerName]);
        }

        $card = $cardRepository->find($id);
        if ($card === null || $card->getGame() === null || $card->getGame()->getId() !== $game->getId() || $card->getIdPlayer() !== 4 || $card->getIsPlayed() !== null) {
            throw $this->createNotFoundException('Card is invalid for this turn.');
        }

        $lastPlayedCard = $cardService->getLastPLayedCard($cardRepository, $game);
        if (!$cardService->isCardPlayable($card, $lastPlayedCard, $game->getPendingDraw())) {
            throw $this->createNotFoundException('Card is not playable.');
        }

        $cardService->playCard($entityManager, $card);

        return $this->redirectToRoute('app_play', ['playerName' => $playerName]);
        
    }
}
