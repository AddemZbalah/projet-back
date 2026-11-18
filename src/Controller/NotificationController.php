<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    /** Liste des notifications ; celles non lues sont mises en avant puis marquées comme lues. */
    #[Route('/notifications', name: 'app_notifications', methods: ['GET'])]
    public function index(NotificationRepository $notificationRepository, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $notifications = $notificationRepository->findBy(['user' => $user], ['created_at' => 'DESC'], 50);
        $unreadIds = [];
        foreach ($notifications as $notification) {
            if (!$notification->isRead()) {
                $unreadIds[] = $notification->getId();
                $notification->setIsRead(true);
            }
        }

        $response = $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
            'unreadIds' => $unreadIds,
        ]);

        // $unreadIds garde la trace des nouvelles pour afficher la pastille « Nouveau » une dernière fois.
        if ($unreadIds !== []) {
            $em->flush();
        }

        return $response;
    }
}
