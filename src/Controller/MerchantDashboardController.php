<?php

namespace App\Controller;

use App\Entity\Commerce;
use App\Entity\CommerceImage;
use App\Entity\Offre;
use App\Entity\OffreImage;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\OffreStatus;
use App\Enum\ReservationStatus;
use App\Form\CommerceType;
use App\Form\OffreType;
use App\Repository\ReservationRepository;
use App\Service\ImageUploader;
use App\Service\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/merchant', name: 'app_merchant_')]
#[IsGranted('ROLE_MERCHANT')]
class MerchantDashboardController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $user = $this->currentUser();
        $commerces = $user->getCommerces()->toArray();

        $online = [];
        $past = [];
        foreach ($commerces as $commerce) {
            foreach ($commerce->getOffres() as $offre) {
                $offre->isOnline() ? $online[] = $offre : $past[] = $offre;
            }
        }
        $reviews = [];
        foreach ($commerces as $commerce) {
            array_push($reviews, ...$commerce->getReviews()->toArray());
        }
        usort($reviews, static fn ($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());
        $averageRating = $reviews === [] ? null
            : round(array_sum(array_map(static fn ($r) => $r->getRating(), $reviews)) / count($reviews), 1);

        usort($online, static fn (Offre $a, Offre $b) => $a->getPickupStart() <=> $b->getPickupStart());
        usort($past, static fn (Offre $a, Offre $b) => $b->getPickupStart() <=> $a->getPickupStart());

        return $this->render('merchant/dashboard.html.twig', [
            'commerces' => $commerces,
            'onlineOffres' => $online,
            'pastOffres' => array_slice($past, 0, 10),
            'upcoming' => $reservationRepository->findUpcomingForOwner($user),
            'stats' => $reservationRepository->getCollectedStatsForOwner($user),
            'reviews' => array_slice($reviews, 0, 12),
            'reviewCount' => count($reviews),
            'averageRating' => $averageRating,
        ]);
    }

    #[Route('/retrait', name: 'pickup_validate', methods: ['POST'])]
    public function validatePickup(Request $request, ReservationRepository $reservationRepository): Response
    {
        $this->assertCsrf('pickup_validate', $request);

        $code = strtoupper(preg_replace('/\s+/', '', (string) $request->request->get('code')));
        $reservation = $code !== '' ? $reservationRepository->findOpenByCodeForOwner($this->currentUser(), $code) : null;

        if (!$reservation instanceof Reservation) {
            $this->addFlash('error', sprintf('Aucune réservation en cours avec le code « %s ».', $code));

            return $this->redirectToRoute('app_merchant_dashboard');
        }

        return $this->markCollected($reservation);
    }

    #[Route('/reservation/{id:reservation}/retiree', name: 'reservation_collect', methods: ['POST'])]
    public function collect(Reservation $reservation, Request $request): Response
    {
        $this->assertCsrf('collect'.$reservation->getId(), $request);
        $this->assertOwnsCommerce($reservation->getOffer()->getCommerce());

        return $this->markCollected($reservation);
    }

    #[Route('/offre/nouvelle', name: 'offre_new', methods: ['GET', 'POST'])]
    public function newOffre(Request $request, Notifier $notifier, ImageUploader $imageUploader): Response
    {
        $commerces = $this->currentUser()->getCommerces()->toArray();
        if ($commerces === []) {
            $this->addFlash('error', 'Inscrivez d’abord votre commerce pour publier une offre.');

            return $this->redirectToRoute('app_commerce_register');
        }

        $start = new \DateTimeImmutable('today 18:00');
        if ($start < new \DateTimeImmutable()) {
            $start = $start->modify('+1 day');
        }

        $offre = (new Offre())
            ->setCommerce($commerces[0])
            ->setPickupStart($start)
            ->setPickupEnd($start->modify('+1 hour'));

        // « Republier » : pré-remplit avec une ancienne offre du commerçant, sur le même horaire au prochain jour possible.
        $source = $request->query->getInt('from') ? $this->em->find(Offre::class, $request->query->getInt('from')) : null;
        if ($source instanceof Offre && $source->getCommerce()?->getOwner() === $this->currentUser()) {
            $start = new \DateTimeImmutable('today '.$source->getPickupStart()->format('H:i'));
            if ($start < new \DateTimeImmutable()) {
                $start = $start->modify('+1 day');
            }
            $duration = $source->getPickupEnd()->getTimestamp() - $source->getPickupStart()->getTimestamp();

            $offre
                ->setCommerce($source->getCommerce())
                ->setTitle($source->getTitle())
                ->setDescription($source->getDescription())
                ->setOriginalPrice($source->getOriginalPrice())
                ->setDiscountedPrice($source->getDiscountedPrice())
                ->setQuantityTotal($source->getQuantityTotal())
                ->setPickupStart($start)
                ->setPickupEnd($start->modify(sprintf('+%d seconds', max(900, $duration))));

            foreach ($source->getPreferences() as $preference) {
                $offre->addPreference($preference);
            }
        }

        // Seulement si l'offre source appartient bien au commerçant connecté (le paramètre « from » vient de l'URL).
        $ownsSource = $source instanceof Offre && $source->getCommerce()?->getOwner() === $this->currentUser();
        $sourceImageCount = $ownsSource ? $source->getImages()->count() : 0;
        $form = $this->createForm(OffreType::class, $offre, ['commerces' => $commerces, 'existing_images' => $sourceImageCount]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->assertOwnsCommerce($offre->getCommerce());
            $offre
                ->setQuantityAvailable($offre->getQuantityTotal())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setStatus([OffreStatus::ACTIVE]);

            if ($sourceImageCount > 0) {
                $imageUploader->copyOffreImages($source, $offre);
            }
            $imageUploader->addToOffre($offre, $form->get('photos')->getData() ?? []);

            $this->em->persist($offre);
            $notified = $notifier->newOffer($offre);
            $this->em->flush();
            $this->addFlash('success', sprintf('« %s » est en ligne !', $offre->getTitle())
                .($notified > 0 ? sprintf(' %d client(s) qui suivent votre commerce ont été prévenus.', $notified) : ''));

            return $this->redirectToRoute('app_merchant_dashboard');
        }

        return $this->render('merchant/offre_form.html.twig', [
            'form' => $form,
            'offre' => $offre,
            'isNew' => true,
            'sourceImages' => $sourceImageCount > 0 ? $source->getImages() : [],
        ]);
    }

    #[Route('/offre/{id:offre}/modifier', name: 'offre_edit', methods: ['GET', 'POST'])]
    public function editOffre(Offre $offre, Request $request, ImageUploader $imageUploader): Response
    {
        $this->assertOwnsCommerce($offre->getCommerce());

        $reserved = $offre->getReservedCount();
        $form = $this->createForm(OffreType::class, $offre, [
            'commerces' => $this->currentUser()->getCommerces()->toArray(),
            'existing_images' => $this->keptImageCount($offre->getImages()->toArray(), $request),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($offre->getQuantityTotal() < $reserved) {
                $form->get('quantityTotal')->addError(new \Symfony\Component\Form\FormError(
                    sprintf('%d panier(s) déjà réservé(s) : la quantité ne peut pas être inférieure.', $reserved)
                ));
            } else {
                $offre->setQuantityAvailable($offre->getQuantityTotal() - $reserved);
                $this->applyImageChanges($offre->getImages()->toArray(), $request, $imageUploader, $offre->removeImage(...));
                $imageUploader->addToOffre($offre, $form->get('photos')->getData() ?? []);
                $this->em->flush();
                $this->addFlash('success', 'Offre mise à jour.');

                return $this->redirectToRoute('app_merchant_dashboard');
            }
        }

        return $this->render('merchant/offre_form.html.twig', [
            'form' => $form,
            'offre' => $offre,
            'isNew' => false,
        ]);
    }

    #[Route('/offre/{id:offre}/retirer', name: 'offre_close', methods: ['POST'])]
    public function closeOffre(Offre $offre, Request $request): Response
    {
        $this->assertCsrf('close'.$offre->getId(), $request);
        $this->assertOwnsCommerce($offre->getCommerce());

        $offre->setStatus([OffreStatus::CANCELLED]);
        $this->em->flush();
        $this->addFlash('success', sprintf('« %s » n’est plus visible par les clients. Les réservations existantes restent valables.', $offre->getTitle()));

        return $this->redirectToRoute('app_merchant_dashboard');
    }

    #[Route('/commerce/{id:commerce}/modifier', name: 'commerce_edit', methods: ['GET', 'POST'])]
    public function editCommerce(Commerce $commerce, Request $request, ImageUploader $imageUploader): Response
    {
        $this->assertOwnsCommerce($commerce);

        $form = $this->createForm(CommerceType::class, $commerce, [
            'existing_images' => $this->keptImageCount($commerce->getImages()->toArray(), $request),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyImageChanges($commerce->getImages()->toArray(), $request, $imageUploader, $commerce->removeImage(...));
            $imageUploader->addToCommerce($commerce, $form->get('photos')->getData() ?? []);
            $this->em->flush();
            $this->addFlash('success', 'Informations du commerce mises à jour.');

            return $this->redirectToRoute('app_merchant_dashboard');
        }

        return $this->render('merchant/commerce_form.html.twig', [
            'form' => $form,
            'commerce' => $commerce,
        ]);
    }

    private function markCollected(Reservation $reservation): Response
    {
        if (!in_array($reservation->getStatus(), [ReservationStatus::Pending, ReservationStatus::Confirmed], true)) {
            $this->addFlash('error', 'Cette réservation n’est plus en cours.');
        } else {
            $reservation->setStatus(ReservationStatus::Collected);
            $this->em->flush();
            $this->addFlash('success', sprintf(
                'Retrait validé : %d × « %s » pour %s.',
                $reservation->getQuantity(),
                $reservation->getOffer()->getTitle(),
                $reservation->getUser()->getUserIdentifier(),
            ));
        }

        return $this->redirectToRoute('app_merchant_dashboard');
    }

    /**
     * Nombre de photos conservées après les suppressions cochées : libère des places
     * dans la limite de photos avant même la validation du formulaire.
     *
     * @param array<CommerceImage|OffreImage> $images
     */
    private function keptImageCount(array $images, Request $request): int
    {
        $deleteIds = array_map('intval', $request->request->all('delete_images'));

        return count(array_filter($images, static fn ($img) => !in_array($img->getId(), $deleteIds, true)));
    }

    /**
     * Applique les choix du formulaire photos : suppressions cochées (fichier + ligne)
     * et photo de couverture choisie, qui passe en première position.
     *
     * @param array<CommerceImage|OffreImage> $images
     * @param callable(CommerceImage|OffreImage): mixed $detach retire l'image de son parent (orphanRemoval supprime la ligne)
     */
    private function applyImageChanges(array $images, Request $request, ImageUploader $uploader, callable $detach): void
    {
        $deleteIds = array_map('intval', $request->request->all('delete_images'));
        $kept = [];
        foreach ($images as $image) {
            if (in_array($image->getId(), $deleteIds, true)) {
                $uploader->remove($image);
                $detach($image);
            } else {
                $kept[] = $image;
            }
        }

        $coverId = $request->request->getInt('cover_image');
        usort($kept, static fn ($a, $b) => [$a->getId() !== $coverId, $a->getPosition()] <=> [$b->getId() !== $coverId, $b->getPosition()]);
        foreach ($kept as $i => $image) {
            $image->setPosition($i);
        }
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user;
    }

    private function assertOwnsCommerce(?Commerce $commerce): void
    {
        if ($commerce === null || $commerce->getOwner() !== $this->currentUser()) {
            throw $this->createAccessDeniedException();
        }
    }

    private function assertCsrf(string $id, Request $request): void
    {
        if (!$this->isCsrfTokenValid($id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Formulaire invalide, veuillez réessayer.');
        }
    }
}
