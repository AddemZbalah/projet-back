<?php

namespace App\Entity;

use App\Enum\OffreStatus;
use App\Repository\OffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $original_price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $discounted_price = null;

    #[ORM\Column]
    private ?int $quantity_total = null;

    #[ORM\Column]
    private ?int $quantity_available = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $pickup_start = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $pickup_end = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'offres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commerce $commerce = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: OffreStatus::class)]
    private array $status = [];

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'offer')]
    private Collection $reservations;

    /**
     * @var Collection<int, Preference>
     */
    #[ORM\ManyToMany(targetEntity: Preference::class, inversedBy: 'offres')]
    #[ORM\JoinTable(name: 'offer_preferences')]
    #[ORM\JoinColumn(name: 'offer_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'preference_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $preferences;

    /**
     * @var Collection<int, OffreImage>
     */
    #[ORM\OneToMany(targetEntity: OffreImage::class, mappedBy: 'offre', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC', 'id' => 'ASC'])]
    private Collection $images;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->preferences = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getOriginalPrice(): ?string
    {
        return $this->original_price;
    }

    public function setOriginalPrice(string $original_price): static
    {
        $this->original_price = $original_price;

        return $this;
    }

    public function getDiscountedPrice(): ?string
    {
        return $this->discounted_price;
    }

    public function setDiscountedPrice(string $discounted_price): static
    {
        $this->discounted_price = $discounted_price;

        return $this;
    }

    public function getQuantityTotal(): ?int
    {
        return $this->quantity_total;
    }

    public function setQuantityTotal(int $quantity_total): static
    {
        $this->quantity_total = $quantity_total;

        return $this;
    }

    public function getQuantityAvailable(): ?int
    {
        return $this->quantity_available;
    }

    public function setQuantityAvailable(int $quantity_available): static
    {
        $this->quantity_available = $quantity_available;

        return $this;
    }

    public function getPickupStart(): ?\DateTimeImmutable
    {
        return $this->pickup_start;
    }

    public function setPickupStart(\DateTimeImmutable $pickup_start): static
    {
        $this->pickup_start = $pickup_start;

        return $this;
    }

    public function getPickupEnd(): ?\DateTimeImmutable
    {
        return $this->pickup_end;
    }

    public function setPickupEnd(\DateTimeImmutable $pickup_end): static
    {
        $this->pickup_end = $pickup_end;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getCommerce(): ?Commerce
    {
        return $this->commerce;
    }

    public function setCommerce(?Commerce $commerce): static
    {
        $this->commerce = $commerce;

        return $this;
    }

    /**
     * @return OffreStatus[]
     */
    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): static
    {
        $this->status = $status;

        return $this;
    }

    /** Offre publiée (statut actif) et dont le créneau n'est pas encore terminé. */
    public function isOnline(): bool
    {
        return in_array(OffreStatus::ACTIVE, $this->status, true)
            && $this->pickup_end !== null
            && $this->pickup_end > new \DateTimeImmutable();
    }

    /** Nombre de paniers déjà réservés (réservations non annulées). */
    public function getReservedCount(): int
    {
        return ($this->quantity_total ?? 0) - ($this->quantity_available ?? 0);
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setOffer($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getOffer() === $this) {
                $reservation->setOffer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Preference>
     */
    public function getPreferences(): Collection
    {
        return $this->preferences;
    }

    public function addPreference(Preference $preference): static
    {
        if (!$this->preferences->contains($preference)) {
            $this->preferences->add($preference);
        }

        return $this;
    }

    public function removePreference(Preference $preference): static
    {
        $this->preferences->removeElement($preference);

        return $this;
    }

    /**
     * @return Collection<int, OffreImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /** Photo de couverture de l'offre, sinon celle du commerce, sinon null. */
    public function getCoverImage(): OffreImage|CommerceImage|null
    {
        return $this->images->first() ?: $this->commerce?->getCoverImage();
    }

    /**
     * Photos à afficher dans la galerie : celles de l'offre d'abord, puis celles du commerce.
     *
     * @return array<OffreImage|CommerceImage>
     */
    public function getGalleryImages(): array
    {
        return [...$this->images->toArray(), ...($this->commerce?->getImages()->toArray() ?? [])];
    }

    public function addImage(OffreImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setOffre($this);
        }

        return $this;
    }

    public function removeImage(OffreImage $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getOffre() === $this) {
                $image->setOffre(null);
            }
        }

        return $this;
    }
}
