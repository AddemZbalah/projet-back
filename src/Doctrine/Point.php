<?php

namespace App\Doctrine;

final class Point
{
    public function __construct(
        public readonly float $longitude,
        public readonly float $latitude,
    ) {}
}