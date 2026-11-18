<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\ReservationStatus;


#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offre $offer = null;

    #[ORM\Column(options: ['default' => 1])]
    private ?int $quantity = 1;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pickupCode = null;

    #[ORM\Column(length: 255, enumType: ReservationStatus::class)]
    private ?ReservationStatus $status = ReservationStatus::Pending;

    #[ORM\Column(nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $reserved_at = null;

    public function __construct()
    {
    $this->reserved_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getOffer(): ?Offre
    {
        return $this->offer;
    }

    public function setOffer(?Offre $offer): static
    {
        $this->offer = $offer;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPickupCode(): ?string
    {
        return $this->pickupCode;
    }

    public function setPickupCode(?string $pickupCode): static
    {
        $this->pickupCode = $pickupCode;

        return $this;
    }

    public function getStatus(): ReservationStatus
    {
    return $this->status;
    }

    public function setStatus(ReservationStatus $status): static
    {
    $this->status = $status;

    return $this;
    }


    public function getReservedAt(): ?\DateTimeImmutable
    {
        return $this->reserved_at;
    }

    public function setReservedAt(?\DateTimeImmutable $reserved_at): static
    {
        $this->reserved_at = $reserved_at;

        return $this;
    }
}
