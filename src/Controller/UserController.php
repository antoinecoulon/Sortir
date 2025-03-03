<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Form\UserType;
use App\Form\UserUploadType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function update(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
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

                if (strpos($firstLine, "\u{FEFF}") === 0) {
                    $firstLine = substr($firstLine, 3);
                }

                $handle = fopen($file->getPathname(), 'r');
                $firstRow = true;
                while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                    if ($firstRow) {
                        $firstRow = false;
                        continue;
                    }

                    if (count($data) < 3) {
                        continue;
                    }

                    [$pseudo, $email, $password, $name, $firstname, $phone, $site, $roles] = $data;
                    $user = new User();
                    $user->setPseudo($pseudo);
                    $user->setEmail($email);
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

                    $em->persist($user);
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
}
