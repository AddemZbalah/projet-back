<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Repository\OffreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OffreController extends AbstractController
{
    #[Route('/offre/{id}', name: 'app_offre_show', methods: ['GET'])]
    public function show(int $id, OffreRepository $offreRepository): Response
    {
        $offre = $offreRepository->find($id);
        if (!$offre instanceof Offre) {
            throw $this->createNotFoundException('Cette offre n’existe pas.');
        }

        return $this->render('offre/show.html.twig', [
            'offre' => $offre,
        ]);
    }
}
