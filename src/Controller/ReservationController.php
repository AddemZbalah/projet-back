<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\ReservationStatus;
use App\Repository\ReservationRepository;
use App\Service\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ReservationController extends AbstractController
{
    #[Route('/mes-reservations', name: 'app_reservations', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findBy(
                ['user' => $this->getUser()],
                ['reserved_at' => 'DESC'],
            ),
        ]);
    }

    #[Route('/offre/{id:offre}/reserver', name: 'app_reservation_create', methods: ['POST'])]
    public function create(Offre $offre, Request $request, EntityManagerInterface $em, Notifier $notifier): Response
    {
        if (!$this->isCsrfTokenValid('reserve'.$offre->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Formulaire de réservation invalide.');
        }

        /** @var User $user */
        $user = $this->getUser();

        // Décrément atomique : évite de réserver plus que le stock si deux clients cliquent en même temps.
        $updated = $em->createQuery(
            'UPDATE App\Entity\Offre o
             SET o.quantity_available = o.quantity_available - 1
             WHERE o.id = :id AND o.quantity_available >= 1'
        )->setParameter('id', $offre->getId())->execute();

        if ($updated === 0) {
            $this->addFlash('error', 'Cette offre n’est plus disponible.');

            return $this->redirectToRoute('app_offre_show', ['id' => $offre->getId()]);
        }

        $reservation = (new Reservation())
            ->setUser($user)
            ->setOffer($offre)
            ->setQuantity(1)
            ->setStatus(ReservationStatus::Confirmed)
            ->setPickupCode(strtoupper(bin2hex(random_bytes(3))));

        $em->persist($reservation);
        $notifier->reservationConfirmed($reservation);
        $em->flush();

        $this->addFlash('success', 'Réservation confirmée ! Ton code de retrait : '.$reservation->getPickupCode());

        return $this->redirectToRoute('app_reservations');
    }

    #[Route('/reservation/{id:reservation}/annuler', name: 'app_reservation_cancel', methods: ['POST'])]
    public function cancel(Reservation $reservation, Request $request, EntityManagerInterface $em): Response
    {
        if ($reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('cancel'.$reservation->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Formulaire d’annulation invalide.');
        }

        if (in_array($reservation->getStatus(), [ReservationStatus::Pending, ReservationStatus::Confirmed], true)) {
            $reservation->setStatus(ReservationStatus::Cancelled);
            $offre = $reservation->getOffer();
            $offre->setQuantityAvailable($offre->getQuantityAvailable() + $reservation->getQuantity());
            $em->flush();
            $this->addFlash('success', 'Réservation annulée.');
        }

        return $this->redirectToRoute('app_reservations');
    }
}
