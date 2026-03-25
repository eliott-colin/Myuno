<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\CardsService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CardRepository;
use DateTimeImmutable;



final class StartController extends AbstractController
{
    #[Route('/start', name: 'app_start')]
    public function index(CardRepository $cardRepository, CardsService $cardsService , EntityManagerInterface $entityManager): Response
    {
        for ($i = 1; $i <= 4; $i++) {
            for ($j = 1; $j <= 7; $j++) {
                $cardsService->initCard($entityManager, $i);
            }
        }
        $cardStart =  $cardsService->initCard($entityManager, 0, new \DateTimeImmutable());      
        $listeCards = $cardRepository->findAll();
        return $this->redirectToRoute("app_play");
    }
}
