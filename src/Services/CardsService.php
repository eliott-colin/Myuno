<?php 

namespace App\Services;

use App\Entity\Card;
use App\Entity\Game;
use App\Repository\CardRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class CardsService {
    public function initCard(EntityManagerInterface $entityManager, int $idPlayer, Game $idGame, ?DateTimeImmutable $isPlayed = null,): void {
        $colors = ['red', 'green', 'blue', 'yellow'];
        $labels = array_merge(range(0, 9), ['S', 'X', '+2']);
        
        $card = new Card();
        $card->setColor($colors[array_rand($colors)]);
        $card->setLabel($labels[array_rand($labels)]);
        $card->setIdPlayer($idPlayer);
        $card->setIsPlayed($isPlayed);
        $card->setGame($idGame);
        $entityManager->persist($card);
        $entityManager->flush();
    }

    public function drawCards(EntityManagerInterface $entityManager, int $idPlayer, Game $game, int $count): void {
        for ($i = 0; $i < $count; $i++) {
            $colors = ['red', 'green', 'blue', 'yellow'];
            $labels = array_merge(range(0, 9), ['S', 'X', '+2']);

            $card = new Card();
            $card->setColor($colors[array_rand($colors)]);
            $card->setLabel((string) $labels[array_rand($labels)]);
            $card->setIdPlayer($idPlayer);
            $card->setIsPlayed(null);
            $card->setGame($game);
            $entityManager->persist($card);
        }

        $entityManager->flush();
    }

    public function getAllCards(CardRepository $cardRepository): array {
        return $cardRepository->findAll();
    }

    public function getCardByPlayer(CardRepository $cardRepository, int $idPlayer, Game $game, ?DateTimeImmutable $isPlayed = null): array {
        return $cardRepository->findBy(['idPlayer' => $idPlayer, 'game' => $game, 'isPlayed' => $isPlayed]);
    }

    public function getCountCardByPLayer(CardRepository $cardRepository, int $idPlayer, Game $game, ?DateTimeImmutable $isPlayed = null): int {
        return count($cardRepository->findBy(['idPlayer' => $idPlayer, 'game' => $game, 'isPlayed' => $isPlayed]));
    }

    public function playCard(EntityManagerInterface $entityManager, Card $card): void {
        $card->setIsPlayed(new DateTimeImmutable());

        $game = $card->getGame();
        if ($game === null) {
            throw new \RuntimeException('Card has no game attached.');
        }

        $this->applyPostPlayEffects($game, (string) $card->getLabel());

        $entityManager->persist($card);
        $entityManager->persist($game);
        $entityManager->flush();
    }

    public function getLastPLayedCard(CardRepository $cardRepository, Game $game): ?Card {
        return $cardRepository->createQueryBuilder('c')
        ->where('c.isPlayed IS NOT NULL')
        ->andWhere('c.game = :game')
        ->setParameter('game', $game)
        ->orderBy('c.isPlayed', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();          
    }

    public function isCardPlayable(Card $card, ?Card $lastPlayedCard, int $pendingDraw): bool {
        if ($lastPlayedCard === null) {
            return true;
        }

        $label = $card->getLabel();
        if ($label === null) {
            return false;
        }

        if ($pendingDraw > 0) {
            return $label === '+2';
        }

        return $card->getColor() === $lastPlayedCard->getColor() || $label === $lastPlayedCard->getLabel();
    }

    public function getPlayableCards(CardRepository $cardRepository, int $idPlayer, Game $game): array {
        $handCards = $this->getCardByPlayer($cardRepository, $idPlayer, $game, null);
        $lastPlayedCard = $this->getLastPLayedCard($cardRepository, $game);
        $pendingDraw = $game->getPendingDraw();

        return array_values(array_filter(
            $handCards,
            fn(Card $card): bool => $this->isCardPlayable($card, $lastPlayedCard, $pendingDraw)
        ));
    }

    public function canPlayerPlay(CardRepository $cardRepository, int $idPlayer, Game $game): bool {
        return count($this->getPlayableCards($cardRepository, $idPlayer, $game)) > 0;
    }

    public function drawAndPassTurn(EntityManagerInterface $entityManager, Game $game, int $idPlayer): void {
        $drawCount = $game->getPendingDraw() > 0 ? $game->getPendingDraw() : 1;
        $this->drawCards($entityManager, $idPlayer, $game, $drawCount);

        if ($game->getPendingDraw() > 0) {
            $game->setPendingDraw(0);
        }

        $this->advancePlayer($game, 1);
        $entityManager->persist($game);
        $entityManager->flush();
    }

    public function playEnemyTurn(EntityManagerInterface $entityManager, CardRepository $cardRepository, Game $game, int $enemyId): void {
        if ($enemyId < 1 || $enemyId > 3) {
            throw new \InvalidArgumentException('Enemy id must be between 1 and 3.');
        }

        if ($game->getCurrentPlayer() !== $enemyId) {
            return;
        }

        $playableCards = $this->getPlayableCards($cardRepository, $enemyId, $game);
        if ($playableCards === []) {
            $this->drawAndPassTurn($entityManager, $game, $enemyId);
            return;
        }

        $randomIndex = array_rand($playableCards);
        $cardToPlay = $playableCards[$randomIndex];
        $this->playCard($entityManager, $cardToPlay);
    }

    private function applyPostPlayEffects(Game $game, string $label): void {
        if ($label === '+2') {
            $game->setPendingDraw($game->getPendingDraw() + 2);
            $this->advancePlayer($game, 1);
            return;
        }

        if ($label === 'S') {
            $game->setDirection($game->getDirection() * -1);
            $this->advancePlayer($game, 1);
            return;
        }

        if ($label === 'X') {
            $this->advancePlayer($game, 2);
            return;
        }

        $this->advancePlayer($game, 1);
    }

    private function advancePlayer(Game $game, int $steps): void {
        $current = $game->getCurrentPlayer();

        for ($i = 0; $i < $steps; $i++) {
            $next = $current + $game->getDirection();
            if ($next > 4) {
                $next = 1;
            }
            if ($next < 1) {
                $next = 4;
            }
            $current = $next;
        }

        $game->setCurrentPlayer($current);
    }
}
