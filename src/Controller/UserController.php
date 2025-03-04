<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Helper\UploadFile;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user', name: 'app_user_')]
#[IsGranted("ROLE_USER")]
final class UserController extends AbstractController
{

    #[Route('/detail/{userId}', name: 'detail',requirements: ['userId' => '\d+'])]
    public function detail(int $userId, UserRepository $userRepository): Response
    {
        $currentUser = $this->getUser();

        $user = $userRepository->findUserById($userId);

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page');
        }

        if ($user->getId() !== $userId) {
            throw $this->createAccessDeniedException( 'Vous n\'avez pas la permission de consulter ce profil');
        }

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }


        return $this->render('user/detail.html.twig', [
            'title' => 'Detail Utilisateur',
            'user' => $user,
        ]);
    }
    #[Route('/update/', name: 'update')]
    public function update(Request $request, UploadFile $uploadFile, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre profil');
        }

        $userForm = $this->createForm(UserType::class, $user, ['is_edit' => true]);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $plainPassword = $userForm->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

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

            $em->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès');

            return $this->redirectToRoute('app_user_detail', ['userId'=>$user->getId()]);
        }

        return $this->render('user/update.html.twig', [
            'title' => 'Modifier l\'Utilisateur',
            'userForm' => $userForm,
        ]);
    }
}
