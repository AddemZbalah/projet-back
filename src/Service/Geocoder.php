<?php

namespace App\Service;

use App\Doctrine\Point;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Geocoder
{
    public function __construct(private HttpClientInterface $httpClient) {}

    public function geocode(?string $address): ?Point
    {
        if (!$address) {
            return null;
        }

        try {
            $data = $this->httpClient->request('GET', 'https://data.geopf.fr/geocodage/search', [
                'query' => ['q' => $address, 'limit' => 1],
                'timeout' => 5,
            ])->toArray();
        } catch (\Throwable) {
            return null; // API indisponible : le commerce est enregistré sans point
        }

        $coords = $data['features'][0]['geometry']['coordinates'] ?? null;

        return $coords ? new Point((float) $coords[0], (float) $coords[1]) : null;
    }
}