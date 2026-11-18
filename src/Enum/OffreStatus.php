<?php

// src/Enum/OffreStatus.php
namespace App\Enum;

enum OffreStatus: string
{
    case ACTIVE = 'active';
    case SOLD_OUT = 'sold_out';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
}