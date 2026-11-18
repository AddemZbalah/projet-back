<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        $errors = [];
        $mail = '';

        if ($request->isMethod('POST')) {
            $mail = trim((string) $request->request->get('mail'));
            $password = (string) $request->request->get('password');
            $passwordConfirmation = (string) $request->request->get('password_confirmation');
            $csrfToken = (string) $request->request->get('_token');

            if (!$csrfTokenManager->isTokenValid(new CsrfToken('register', $csrfToken))) {
                $errors[] = 'Le formulaire a expiré. Veuillez recommencer.';
            }

            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Veuillez saisir une adresse e-mail valide.';
            } elseif ($userRepository->findOneBy(['mail' => $mail])) {
                $errors[] = 'Cette adresse e-mail est déjà utilisée.';
            }

            if (strlen($password) < 8) {
                $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
            }

            if ($password !== $passwordConfirmation) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            if ($errors === []) {
                $user = new User();
                $user->setMail($mail);
                $user->setPassword($passwordHasher->hashPassword($user, $password));
                $user->setRoles([]);
                $user->setCreatedAt(new \DateTimeImmutable());

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/register.html.twig', [
            'errors' => $errors,
            'mail' => $mail,
        ]);
    }
}
