<?php

namespace App\Controller;

use App\Entity\Event;
use App\Enum\EventState;
use App\Form\EventType;

use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class EventController extends AbstractController
{
    private readonly EventRepository $eventRepository;
    private readonly EntityManagerInterface $em;

    public function __construct(EventRepository $eventRepository, EntityManagerInterface $em)
    {
        $this->eventRepository = $eventRepository;
        $this->em = $em;
    }

    #[Route('/', name: 'app_event', methods: ['GET'])]
    public function index(): Response
    {
        $events = $this->eventRepository->findAll();

        $inscriptionsCount = $this->eventRepository->findInscriptionUserCount(); // On récupère un tableau associatif
        // et on le transforme en tableau indexé par l'ID
        $inscriptionsCountById = [];
        foreach ($inscriptionsCount as $count) {
            $inscriptionsCountById[$count['eventId']] = $count['inscriptionCount'];
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'inscriptionCount' => $inscriptionsCountById,
        ]);
    }

    #[Route('/event/create', name: 'app_event_create')]
    // #[IsGranted("ROLE_USER")]
    public function create(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($event);
            $this->em->flush();
        }

        return $this->render('event/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/event/detail/{id}', name: 'app_event_detail', requirements: ['id' => '\d+'])]
    public function detail(Event $event): Response
    {
        return $this->render('event/detail.html.twig');
    }

    #[Route('/event/update/{id}', name: 'app_event_update', requirements: ['id' => '\d+'])]
    public function update(Event $event): Response
    {
        return $this->render('event/detail.html.twig');
    }

    #[Route('/event/delete/{id}', name: 'app_event_delete', requirements: ['id' => '\d+'])]
    public function delete(Event $event): Response
    {
        $this->addFlash('success', "l'event a bien été supprimé");
        return $this->redirectToRoute('app_event');
    }
}
