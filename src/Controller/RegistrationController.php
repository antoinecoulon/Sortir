<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Helper\UploadFile;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;


class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UploadFile $uploadFile, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_event');
            }

        }
        $user = new User();
        $userForm = $this->createForm(UserType::class, $user, ['is_edit' => false]);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $userForm->get('plainPassword')->getData();

            // Upload profile picture
            try {
                if ($userForm->get('profilePicture')->getData()) {
                    $file = $userForm->get('profilePicture')->getData();
                    $name = $uploadFile->upload($file, $user->getPseudo(), $this->getParameter('kernel.project_dir') . '/public/uploads');
                    $user->setPhoto($name);
                }
            } catch (FileException $e) {
                $userForm->addError(new FormError($e->getMessage()));
            }

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);
            $user->setIsActive(true);
            if ($this->isGranted('ROLE_ADMIN')) {
                $user->setIsVerified(true);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Utilisateur créé avec succès');
                return $this->redirectToRoute('app_user_list');
            }
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Un mail de confirmation vient de vous être envoyé');

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@campus-eni.fr', 'Administrateur Sortir'))
                    ->to((string) $user->getEmail())
                    ->subject('Veuillez confirmer votre adresse email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'userForm' => $userForm,
            'title' =>'Créer un compte'
        ]);
    }

    #[Route('/verify', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, TranslatorInterface $translator): Response
    {
        $id = $request->query->get('id'); // On récupère l'identifiant de l'utilisateur provenant de l'url

        // On vérifie que l'identifiant existe et n'est pas null
        if ($id === null) {
            return $this->redirectToRoute('app_login');
        }

        $user = $userRepository->find($id);
        // On s'assure que l'utilisateur existe en persistance
        if ($user === null) {
            return $this->redirectToRoute('app_login');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }


        $this->addFlash('success', 'Votre adresse email est vérifiée');

        return $this->redirectToRoute('app_login');
    }
}
