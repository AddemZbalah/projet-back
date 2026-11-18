<?php

namespace App\Service;

use App\Entity\Commerce;
use App\Entity\Notification;
use App\Entity\Offre;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\NotificationType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Crée les notifications in-app. Ne flush pas : l'appelant flush avec le reste de son traitement.
 */
class Notifier
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function notify(User $user, NotificationType $type, string $message): Notification
    {
        $notification = (new Notification())
            ->setUser($user)
            ->setType($type)
            ->setMessage($message)
            ->setIsRead(false);

        $this->em->persist($notification);

        return $notification;
    }

    public function reservationConfirmed(Reservation $reservation): void
    {
        $offre = $reservation->getOffer();

        $this->notify(
            $reservation->getUser(),
            NotificationType::ReservationConfirmee,
            sprintf(
                'Votre réservation « %s » chez %s est confirmée. Code de retrait : %s — retrait le %s entre %s et %s.',
                $offre->getTitle(),
                $offre->getCommerce()->getName(),
                $reservation->getPickupCode(),
                $offre->getPickupStart()->format('d/m'),
                $offre->getPickupStart()->format('H:i'),
                $offre->getPickupEnd()->format('H:i'),
            ),
        );
    }

    /** Prévient les clients qui ont mis le commerce en favori. */
    public function newOffer(Offre $offre): int
    {
        $commerce = $offre->getCommerce();
        $count = 0;

        foreach ($commerce->getFavorites() as $favorite) {
            $this->notify(
                $favorite->getUser(),
                NotificationType::NouvelleOffre,
                sprintf('%s, l’un de vos commerces favoris, propose « %s » à %s €.', $commerce->getName(), $offre->getTitle(), number_format((float) $offre->getDiscountedPrice(), 2, ',', ' ')),
            );
            ++$count;
        }

        return $count;
    }
}
