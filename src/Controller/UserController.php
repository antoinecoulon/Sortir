<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Form\UserType;
use App\Form\UserUploadType;
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

    #[Route('/import/', name: 'import')]
    public function import(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if ($this->getUser()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_event');
            }
        }

        $form = $this->createForm(UserUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('csvFile')->getData();

            if ($file) {
                $handle = fopen($file->getPathname(), 'r');
                $firstLine = fgets($handle);

                if (str_starts_with($firstLine, "\u{FEFF}")) {
                    $firstLine = substr($firstLine, 3);
                }


                $firstRow = true;
                while (($data = fgetcsv($handle, 1000, ';')) !== false) {

                    if (count($data) < 8) {
                        continue;
                    }

                    [$pseudo, $email, $password, $name, $firstname, $phone, $site, $roles] = $data;

                    try {
                        $user = new User();
                        $user->setPseudo($pseudo);
                        $user->setEmail($email);

                        if (strlen($password) < 5) {
                            $this->addFlash('error', "Mot de passe trop court pour $pseudo");
                            continue;
                        }

                        $user->setPassword($userPasswordHasher->hashPassword($user, $password));
                        $user->setName($name);
                        $user->setFirstname($firstname);
                        $user->setPhone($phone);

                        $siteEntity = $em->getRepository(Site::class)->findOneBy(['name' => $site]);
                        if (!$siteEntity) {
                            $this->addFlash('error', "Le site'$site' n'existe pas en base");
                            continue;
                        }

                        $user->setSite($siteEntity);
                        $user->setRoles(explode('|', $roles));
                        $user->setIsActive(true);
                        $user->setIsVerified(true);

                        $em->persist($user);
                    } catch (\Exception $e) {
                        $this->addFlash('error', "Erreur lors de la création de l'utilisateur $pseudo : " . $e->getMessage());
                        continue;
                    }
                }
                    fclose($handle);
                    $em->flush();
                    $this->addFlash('success', 'Utilisateurs importés avec succès');
                    return $this->redirectToRoute('app_event');
                }
            }

        return $this->render('user/import.html.twig', [
            'form' => $form,
            'title' => 'Import des utilisateurs',
        ]);
    }

    #[Route('/list', name: 'list')]
    public function list(UserRepository $userRepository): Response
    {
        if ($this->getUser()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_event');
            }
        }
        $users = $userRepository->findAll();

        return $this->render('user/list.html.twig', [
            'users' => $users,
            'title' => 'Liste des utilisateurs',
        ]);
    }

    #[Route('/desactivate/{userId}', name: 'desactivate', requirements: ['userId' => '\d+'])]
    public function desactivate(int $userId, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Accès interdit");
        }

        $user = $userRepository->findUserById($userId);

        $user->setIsActive(false);

        $em->flush();
        $this->addFlash('success', "Le compte de l'utilisateur {$user->getFirstname()} a bien été désactivé");
        return $this->redirectToRoute("app_user_list");
    }

    #[Route('/activate/{userId}', name: 'activate', requirements: ['userId' => '\d+'])]
    public function activate(int $userId, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Accès interdit");
        }

        $user = $userRepository->findUserById($userId);

        $user->setIsActive(true);

        $em->flush();
        $this->addFlash('success', "Le compte de l'utilisateur {$user->getFirstname()} a bien été réactivé");
        return $this->redirectToRoute("app_user_list");
    }

    #[Route('/delete/{userId}', name: 'delete', requirements: ['userId' => '\d+'])]
    public function delete(int $userId, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Accès interdit");
        }
        $user = $userRepository->findUserById($userId);

       $em->remove($user);
       $em->flush();
        $this->addFlash('success', "Le compte de l'utilisateur {$user->getFirstname()} a bien été supprimé");
        return $this->redirectToRoute("app_user_list");
    }
}
