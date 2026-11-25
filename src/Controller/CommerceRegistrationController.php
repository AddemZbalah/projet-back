<?php

namespace App\Controller;

use App\Entity\Commerce;
use App\Entity\User;
use App\Form\CommerceType;
use App\Repository\UserRepository;
use App\Service\ImageUploader;
use App\Service\Geocoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CommerceRegistrationController extends AbstractController
{
    #[Route('/commerce/register', name: 'app_commerce_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        CsrfTokenManagerInterface $csrfTokenManager,
        ImageUploader $imageUploader,
        Geocoder $geocoder,
    ): Response {
        $commerce = new Commerce();
        $form = $this->createForm(CommerceType::class, $commerce);
        $form->handleRequest($request);

        $user = $this->getUser();
        $isAuthenticatedUser = $user instanceof User;
        $accountErrors = [];
        $mail = '';

        if ($form->isSubmitted()) {
            if (!$isAuthenticatedUser) {
                $mail = trim((string) $request->request->get('mail'));
                $password = (string) $request->request->get('password');
                $passwordConfirmation = (string) $request->request->get('password_confirmation');
                $csrfToken = (string) $request->request->get('_account_token');

                if (!$csrfTokenManager->isTokenValid(new CsrfToken('commerce_register', $csrfToken))) {
                    $accountErrors[] = 'Le formulaire a expiré. Veuillez recommencer.';
                }

                if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                    $accountErrors[] = 'Veuillez saisir une adresse e-mail valide.';
                } elseif ($userRepository->findOneBy(['mail' => $mail])) {
                    $accountErrors[] = 'Cette adresse e-mail est déjà utilisée.';
                }

                if (strlen($password) < 8) {
                    $accountErrors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
                }

                if ($password !== $passwordConfirmation) {
                    $accountErrors[] = 'Les mots de passe ne correspondent pas.';
                }

                if (!$request->request->getBoolean('cgu')) {
                    $accountErrors[] = 'Vous devez accepter les conditions générales d’utilisation.';
                }

                if ($accountErrors === []) {
                    $now = new \DateTimeImmutable();
                    $user = new User();
                    $user->setMail($mail);
                    $user->setPassword($passwordHasher->hashPassword($user, $password));
                    $user->setRoles(['ROLE_MERCHANT']);
                    $user->setCreatedAt($now);
                    $user->setCguValidated($now);
                }
            }

            if ($form->isValid() && $accountErrors === []) {
                if (!$user instanceof User) {
                    throw $this->createAccessDeniedException();
                }

                $commerce->setOwner($user);
                $commerce->setCreatedAt(new \DateTimeImmutable());

                $roles = $user->getRoles();
                if (!in_array('ROLE_MERCHANT', $roles, true)) {
                    $roles[] = 'ROLE_MERCHANT';
                    $user->setRoles($roles);
                }

                $imageUploader->addToCommerce($commerce, $form->get('photos')->getData() ?? []);
                $commerce->setLocation($geocoder->geocode($commerce->getAddress()));

                $entityManager->persist($user);
                $entityManager->persist($commerce);
                $entityManager->flush();

                return $this->redirectToRoute($isAuthenticatedUser ? 'app_merchant_dashboard' : 'app_login');
            }
        }

        return $this->render('commerce/registration.html.twig', [
            'form' => $form,
            'accountErrors' => $accountErrors,
            'isAuthenticatedUser' => $isAuthenticatedUser,
            'mail' => $mail,
        ]);
    }
}
