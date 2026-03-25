<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\CardsService;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;

final class PlayerController extends AbstractController
{
    #[Route('/player/{id}', name: 'app_player')]
    public function index(EntityManagerInterface $entityManager, CardsService $cardService, string $id , CardRepository $cardRepository): Response
    {
        $cardPlayed = $cardService->playCard($entityManager, $cardRepository, $id);
        return $this->redirectToRoute("app_play");
        
    }
}
