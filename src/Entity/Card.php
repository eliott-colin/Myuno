<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column]
    private ?int $idPlayer = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $isPlayed = null;

    #[ORM\Column(length: 255)]
    private ?string $color = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getIdPlayer(): ?int
    {
        return $this->idPlayer;
    }

    public function setIdPlayer(int $idPlayer): static
    {
        $this->idPlayer = $idPlayer;

        return $this;
    }

    public function getIsPlayed(): ?\DateTimeImmutable
    {
        return $this->isPlayed;
    }

    public function setIsPlayed(?\DateTimeImmutable $isPlayed): static
    {
        $this->isPlayed = $isPlayed;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }
}
