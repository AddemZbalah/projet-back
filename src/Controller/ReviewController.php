<?php

namespace App\Controller;

use App\Entity\Commerce;
use App\Entity\Review;
use App\Entity\User;
use App\Enum\ReservationStatus;
use App\Form\ReviewType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ReviewController extends AbstractController
{
    /**
     * Donner (ou modifier) son avis sur un commerce.
     * Réservé aux clients ayant déjà récupéré au moins un panier dans ce commerce ; un seul avis par client.
     */
    #[Route('/commerce/{id:commerce}/avis', name: 'app_review_form', methods: ['GET', 'POST'])]
    public function form(Commerce $commerce, Request $request, ReservationRepository $reservationRepository, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $hasCollected = (bool) $reservationRepository->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.offer', 'o')
            ->andWhere('r.user = :user')
            ->andWhere('o.commerce = :commerce')
            ->andWhere('r.status = :collected')
            ->setParameter('user', $user)
            ->setParameter('commerce', $commerce)
            ->setParameter('collected', ReservationStatus::Collected->value)
            ->getQuery()
            ->getSingleScalarResult();

        if (!$hasCollected) {
            $this->addFlash('error', 'Vous pourrez donner votre avis après avoir récupéré un panier chez ce commerçant.');

            return $this->redirectToRoute('app_reservations');
        }

        $review = $user->getReviewFor($commerce);
        $isNew = $review === null;
        $review ??= (new Review())->setUser($user)->setCommerce($commerce);

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($isNew) {
                $em->persist($review);
            }
            $em->flush();
            $this->addFlash('success', 'Merci pour votre avis !');

            return $this->redirectToRoute('app_reservations');
        }

        return $this->render('review/form.html.twig', [
            'form' => $form,
            'commerce' => $commerce,
            'isNew' => $isNew,
        ]);
    }
}
