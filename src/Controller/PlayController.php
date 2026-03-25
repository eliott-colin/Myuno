<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\CardsService;
use App\Repository\CardRepository;

final class PlayController extends AbstractController
{
    #[Route('/play', name: 'app_play')]
    public function index(CardRepository $cardRepository, CardsService $cardsService): Response
    {
        $cards = $cardsService->getCardByPlayer($cardRepository, 1);
        $cardCount = [];
        for($i = 4; $i >= 1; $i--){
            $cardCount = [...$cardCount,($cardsService->getCountCardByPLayer($cardRepository,$i))];
        }
        $cardInit = $cardsService->getLastPLayedCard($cardRepository);
        return $this->render('play/index.html.twig', [
            'controller_name' => 'PlayController',
            'cards' => $cards,
            'cardCount' => $cardCount,
            'cardInit' => $cardInit
        ]);
    }
}
