<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PreferenceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profil', name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        CsrfTokenManagerInterface $csrfTokenManager,
        PreferenceRepository $preferenceRepository,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $mail = $user->getMail() ?? '';
        $errors = [];
        $updated = false;

        if ($request->isMethod('POST')) {
            $mail = trim((string) $request->request->get('mail'));
            $password = (string) $request->request->get('password');
            $passwordConfirmation = (string) $request->request->get('password_confirmation');

            if (!$csrfTokenManager->isTokenValid(new CsrfToken('profile', (string) $request->request->get('_token')))) {
                $errors[] = 'Le formulaire a expiré. Veuillez recommencer.';
            }

            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Veuillez saisir une adresse e-mail valide.';
            } else {
                $existingUser = $userRepository->findOneBy(['mail' => $mail]);
                if ($existingUser !== null && $existingUser !== $user) {
                    $errors[] = 'Cette adresse e-mail est déjà utilisée.';
                }
            }

            if ($password !== '' || $passwordConfirmation !== '') {
                if (strlen($password) < 8) {
                    $errors[] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
                }

                if ($password !== $passwordConfirmation) {
                    $errors[] = 'Les nouveaux mots de passe ne correspondent pas.';
                }
            }

            if ($errors === []) {
                $user->setMail($mail);
                if ($password !== '') {
                    $user->setPassword($passwordHasher->hashPassword($user, $password));
                }

                $entityManager->flush();
                $updated = true;
            }
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'mail' => $mail,
            'errors' => $errors,
            'updated' => $updated,
            'preferences' => $preferenceRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/profil/preferences', name: 'app_profile_preferences', methods: ['POST'])]
    public function preferences(Request $request, PreferenceRepository $preferenceRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('preferences', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Le formulaire a expiré. Veuillez recommencer.');
        }

        $selectedIds = array_map('intval', $request->request->all('preferences'));

        foreach ($preferenceRepository->findAll() as $preference) {
            in_array($preference->getId(), $selectedIds, true)
                ? $user->addPreference($preference)
                : $user->removePreference($preference);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Vos préférences ont été enregistrées. Les offres compatibles sont mises en avant sur l’accueil.');

        return $this->redirectToRoute('app_profile');
    }
}
