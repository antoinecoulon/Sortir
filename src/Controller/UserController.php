<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user', name: 'app_user_')]
#[IsGranted("ROLE_USER")]
final class UserController extends AbstractController
{

    #[Route('/detail', name: 'detail',requirements: ['userId' => '\d+'])]
    public function detail(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page');
        }
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }


        return $this->render('user/detail.html.twig', [
            'title' => 'Detail Utilisateur',
            'user' => $user,
        ]);
    }
    #[Route('/update/', name: 'update',requirements: ['userId' => '\d+'])]
    public function update(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre profil');
        }

        $userForm = $this->createForm(UserType::class, $user);

        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
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
