<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use App\Service\GroupService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("IS_AUTHENTICATED")]
final class GroupController extends AbstractController
{
    private readonly GroupRepository $groupRepository;
    private readonly EntityManagerInterface $em;
    private readonly GroupService $groupService;

    public function __construct(GroupRepository $groupRepository, EntityManagerInterface $em, GroupService $groupService)
    {
        $this->groupRepository = $groupRepository;
        $this->em = $em;
        $this->groupService = $groupService;
    }

    #[Route('/group', name: 'app_group')]
    public function index(): Response
    {
        $creator = $this->getUser();
        $groups = $this->groupRepository->findBy(['creator' => $creator]);
        return $this->render('group/index.html.twig', [
            'groups' => $groups
        ]);
    }

    #[Route('/group/create', name: 'app_group_create')]
    public function create(Request $request): Response
    {
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $group->setCreator($this->getUser());
            $this->em->persist($group);
            $this->em->flush();
            $this->addFlash('success', "Le groupe {$group->getName()} a bien été crée");
            return $this->redirectToRoute('app_group_detail', ['id' => $group->getId()]);
        }
        return $this->render('group/save.html.twig', [
            "form" => $form,
            "group" => $group
        ]);
    }

    #[Route('/group/detail/{id}', name: 'app_group_detail', requirements: ['id' => '\d+'])]
    public function detail(Group $group): Response
    {
        if(!$this->groupService->isGroupCreator($group, $this->getUser())) {
            throw $this->createAccessDeniedException("Accès interdit");
        }
        return $this->render('group/detail.html.twig', [
            "group" => $group
        ]);
    }

    #[Route('/group/update/{id}', name: 'app_group_update', requirements: ['id' => '\d+'])]
    public function update(Group $group, Request $request): Response
    {
        if(!$this->groupService->isGroupCreator($group, $this->getUser())) {
            throw $this->createAccessDeniedException("Accès interdit");
        }
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($group);
            $this->em->flush();
            $this->addFlash('success', "Le groupe {$group->getName()} a bien été modifié");
            return $this->redirectToRoute('app_group_detail', ['id' => $group->getId()]);
        }
        return $this->render('group/save.html.twig', [
            "form" => $form,
            "group" => $group
        ]);
    }

    #[Route('/group/delete/{id}', name: 'app_group_delete', requirements: ['id' => '\d+'])]
    public function delete(Group $group): Response
    {
        if(!$this->groupService->isGroupCreator($group, $this->getUser())) {
            throw $this->createAccessDeniedException("Accès interdit");
        }
        $this->em->remove($group);
        $this->em->flush();
        $this->addFlash('success', "Le groupe {$group->getName()} a bien été supprimé");
        return $this->redirectToRoute("app_group");
    }
}
