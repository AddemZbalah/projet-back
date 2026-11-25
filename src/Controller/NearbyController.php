<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Repository\CommerceRepository;
use App\Service\Geocoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/** Carte « Autour de moi » : commerces proches d'une position ou d'une adresse. */
class NearbyController extends AbstractController
{
    /** Rayons proposés à l'utilisateur, en kilomètres. */
    public const RADII_KM = [1, 2, 5, 10];
    private const DEFAULT_RADIUS_KM = 2;

    #[Route('/autour-de-moi', name: 'app_nearby', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('nearby/index.html.twig', [
            'radii' => self::RADII_KM,
            'defaultRadius' => self::DEFAULT_RADIUS_KM,
        ]);
    }

    /**
     * Données JSON pour la carte.
     * Paramètres : soit `lat` + `lng` (géolocalisation du navigateur), soit `q` (adresse saisie), et `radius` en km.
     */
    #[Route('/autour-de-moi/commerces', name: 'app_nearby_data', methods: ['GET'])]
    public function data(Request $request, CommerceRepository $commerceRepository, Geocoder $geocoder): JsonResponse
    {
        $radius = $request->query->getInt('radius', self::DEFAULT_RADIUS_KM);
        if (!in_array($radius, self::RADII_KM, true)) {
            $radius = self::DEFAULT_RADIUS_KM;
        }

        $query = trim((string) $request->query->get('q', ''));
        if ($query !== '') {
            $point = $geocoder->geocode($query);
            if ($point === null) {
                return $this->json(['error' => 'Adresse introuvable. Essayez avec une ville ou un code postal.'], Response::HTTP_NOT_FOUND);
            }
            $lat = $point->latitude;
            $lng = $point->longitude;
        } else {
            $lat = filter_var($request->query->get('lat'), FILTER_VALIDATE_FLOAT);
            $lng = filter_var($request->query->get('lng'), FILTER_VALIDATE_FLOAT);
            if ($lat === false || $lng === false || abs($lat) > 90 || abs($lng) > 180) {
                return $this->json(['error' => 'Position invalide.'], Response::HTTP_BAD_REQUEST);
            }
        }

        $results = [];
        foreach ($commerceRepository->findNear($lat, $lng, $radius * 1000) as ['commerce' => $commerce, 'distance' => $distance]) {
            $offers = $commerce->getOffres()->filter(
                static fn (Offre $o) => $o->isOnline() && $o->getQuantityAvailable() > 0
            );
            $firstOffer = $offers->first() ?: null;

            $results[] = [
                'id' => $commerce->getId(),
                'name' => $commerce->getName(),
                'category' => $commerce->getCategory() ?: 'Commerce',
                'address' => $commerce->getAddress(),
                'lat' => $commerce->getLocation()->latitude,
                'lng' => $commerce->getLocation()->longitude,
                'distance' => (int) round($distance),
                'rating' => $commerce->getAverageRating(),
                'image' => $commerce->getCoverImage()?->getUrl(),
                'offersCount' => $offers->count(),
                'offerUrl' => $firstOffer ? $this->generateUrl('app_offre_show', ['id' => $firstOffer->getId()]) : null,
            ];
        }

        return $this->json([
            'center' => ['lat' => $lat, 'lng' => $lng],
            'radius' => $radius,
            'commerces' => $results,
        ]);
    }
}
