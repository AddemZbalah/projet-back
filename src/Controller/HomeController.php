<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Entity\User;
use App\Repository\OffreRepository;
use App\Repository\PreferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(OffreRepository $offreRepository, PreferenceRepository $preferenceRepository): Response
    {
        $offres = $offreRepository->findActiveOffers(24);
        $user = $this->getUser();
        $userPrefIds = $user instanceof User ? $user->getPreferenceIds() : [];

        // Offres compatibles avec TOUTES les préférences de l'utilisateur : mises en avant en tête de liste.
        $matchingIds = [];
        if ($userPrefIds !== []) {
            foreach ($offres as $offre) {
                $offrePrefIds = $offre->getPreferences()->map(static fn ($p) => $p->getId())->getValues();
                if (array_diff($userPrefIds, $offrePrefIds) === []) {
                    $matchingIds[] = $offre->getId();
                }
            }
            usort($offres, static fn (Offre $a, Offre $b) => in_array($b->getId(), $matchingIds, true) <=> in_array($a->getId(), $matchingIds, true));
        }

        return $this->render('home/index.html.twig', [
            'offres' => $offres,
            'preferences' => $preferenceRepository->findBy([], ['name' => 'ASC']),
            'matchingIds' => $matchingIds,
            'userPrefIds' => $userPrefIds,
        ]);
    }
}
