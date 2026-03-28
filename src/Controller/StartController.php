<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\CardsService;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\GameService;
use Symfony\Component\HttpFoundation\Request;



final class StartController extends AbstractController
{
    #[Route('/start', name: 'app_start', methods: ['POST'])]
    public function index(Request $request, CardsService $cardsService , EntityManagerInterface $entityManager, GameService $gameService): Response
    {
        $value = trim((string) $request->request->get('pseudo'));
        if ($value === '') {
            return $this->redirectToRoute('app_home');
        }

        $game = $gameService->initGame($value, $entityManager);

        for ($i = 1; $i <= 4; $i++) {
            for ($j = 1; $j <= 7; $j++) {
                $cardsService->initCard($entityManager, $i, $game, null);
            }
        }

        $cardsService->initCard($entityManager, 0, $game, new \DateTimeImmutable());

        return $this->redirectToRoute('app_play', ['playerName' => $value]);
    }
}
