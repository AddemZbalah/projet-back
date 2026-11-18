<?php

namespace App\Controller;

use App\Entity\Commerce;
use App\Entity\Favorite;
use App\Entity\User;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class FavoriteController extends AbstractController
{
    #[Route('/favoris', name: 'app_favorites', methods: ['GET'])]
    public function index(FavoriteRepository $favoriteRepository): Response
    {
        return $this->render('favorite/index.html.twig', [
            'favorites' => $favoriteRepository->findBy(['user' => $this->getUser()], ['created_at' => 'DESC']),
        ]);
    }

    /** Ajoute ou retire un commerce des favoris, puis revient sur la page d'origine. */
    #[Route('/commerce/{id:commerce}/favori', name: 'app_favorite_toggle', methods: ['POST'])]
    public function toggle(Commerce $commerce, Request $request, FavoriteRepository $favoriteRepository, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('favorite'.$commerce->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Formulaire invalide, veuillez réessayer.');
        }

        /** @var User $user */
        $user = $this->getUser();
        $favorite = $favoriteRepository->find(['user' => $user, 'commerce' => $commerce]);

        if ($favorite instanceof Favorite) {
            // removeElement plutôt que removeFavorite() : ce dernier remet user à null, or c'est une partie de la clé primaire.
            $user->getFavorites()->removeElement($favorite);
            $commerce->getFavorites()->removeElement($favorite);
            $em->remove($favorite);
            $isFavorite = false;
            $message = sprintf('%s a été retiré de vos favoris.', $commerce->getName());
        } else {
            $favorite = new Favorite($user, $commerce);
            $user->getFavorites()->add($favorite);
            $commerce->getFavorites()->add($favorite);
            $em->persist($favorite);
            $isFavorite = true;
            $message = sprintf('%s est dans vos favoris : vous serez prévenu de ses nouvelles offres.', $commerce->getName());
        }

        $em->flush();

        // Appel fetch() depuis le front : réponse JSON, pas de rechargement de page.
        if ($request->getPreferredFormat() === 'json') {
            return $this->json(['favorite' => $isFavorite, 'message' => $message]);
        }

        // Sans JavaScript : comportement classique (flash + retour sur la page d'origine).
        $this->addFlash('success', $message);

        return $this->redirect($this->safeReferer($request) ?? $this->generateUrl('app_favorites'));
    }

    private function safeReferer(Request $request): ?string
    {
        $referer = (string) $request->headers->get('referer');

        return $referer !== '' && str_starts_with($referer, $request->getSchemeAndHttpHost().'/') ? $referer : null;
    }
}
