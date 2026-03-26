<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\CardsService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CardRepository;
use App\Services\GameService;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\GameRepository;



final class StartController extends AbstractController
{
    #[Route('/start', name: 'app_start', methods: ['POST'])]
    public function index(Request $request, CardRepository $cardRepository, CardsService $cardsService , EntityManagerInterface $entityManager, GameService $gameService, GameRepository $gameRepository): Response
    {
        $value = $request->request->get('pseudo');
        $gameService = new GameService();
        $gameAlreadyCreate = $gameService->initGame($value, $gameRepository ,$entityManager);
        $idGame = $gameService->getIdGameByPlayerName($value, $gameRepository);
        if($gameAlreadyCreate === false){
            for ($i = 1; $i <= 4; $i++) {
            for ($j = 1; $j <= 7; $j++) {
                $cardsService->initCard($entityManager, $i , $idGame ,null,);
            }
        }
        $cardStart =  $cardsService->initCard($entityManager, 0,$idGame, new \DateTimeImmutable());
        }
        $listeCards = $cardRepository->findAll();
        return $this->redirectToRoute("app_play", ['playerName' => $value]);    
    }
}
