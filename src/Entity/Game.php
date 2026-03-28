<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $player = null;

    #[ORM\Column]
    private int $currentPlayer = 4;

    #[ORM\Column]
    private int $direction = 1;

    #[ORM\Column]
    private int $pendingDraw = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?string
    {
        return $this->player;
    }

    public function setPlayer(string $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getCurrentPlayer(): int
    {
        return $this->currentPlayer;
    }

    public function setCurrentPlayer(int $currentPlayer): static
    {
        $this->currentPlayer = $currentPlayer;

        return $this;
    }

    public function getDirection(): int
    {
        return $this->direction;
    }

    public function setDirection(int $direction): static
    {
        $this->direction = $direction;

        return $this;
    }

    public function getPendingDraw(): int
    {
        return $this->pendingDraw;
    }

    public function setPendingDraw(int $pendingDraw): static
    {
        $this->pendingDraw = $pendingDraw;

        return $this;
    }
}
