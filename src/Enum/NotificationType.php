<?php

namespace App\Enum;

enum NotificationType: string
{
    case NouvelleOffre = 'nouvelle_offre';
    case RappelRetrait = 'rappel_retrait';
    case ReservationConfirmee = 'reservation_confirmee';
}
