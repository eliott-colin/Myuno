<?php 

namespace App\Services;

use App\Entity\Card;
use App\Repository\CardRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class CardsService {
    public function initCard(EntityManagerInterface $entityManager, int $idPlayer, ?DateTimeImmutable $isPlayed = null): void {
        $colors = ['red', 'green', 'blue', 'yellow'];
        $labels = array_merge(range(0, 9), ['S', 'X', '+2']);
        
        $card = new Card();
        $card->setColor($colors[array_rand($colors)]);
        $card->setLabel($labels[array_rand($labels)]);
        $card->setIdPlayer($idPlayer);
        $card->setIsPlayed($isPlayed);
        $entityManager->persist($card);
        $entityManager->flush();
    }

    public function getAllCards(CardRepository $cardRepository): array {
        return $cardRepository->findAll();
    }
    public function getCardByPlayer(CardRepository $cardRepository, int $idPlayer, ?DateTimeImmutable $isPlayed = null): array {
        return $cardRepository->findBy(['idPlayer' => $idPlayer, 'isPlayed' => $isPlayed]);
    }
    public function getCountCardByPLayer(CardRepository $cardRepository, int $idPlayer , ?DateTimeImmutable $isPlayed = null): int {
        return count($cardRepository->findBy(['idPlayer' => $idPlayer, 'isPlayed' => $isPlayed]));
    }
    public function playCard(EntityManagerInterface $entityManager, CardRepository $cardRepository, string $id): void {
        $card = $cardRepository->findOneBy(['id' => $id]);
        $card->setIsPlayed(new DateTimeImmutable());
        $entityManager->persist($card);
        $entityManager->flush();
    }
    public function getLastPLayedCard(CardRepository $cardRepository): ?Card {
        return $cardRepository->createQueryBuilder('c')
        ->where('c.isPlayed IS NOT NULL')
        ->orderBy('c.isPlayed', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();          
    }
}
