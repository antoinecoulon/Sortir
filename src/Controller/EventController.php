<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Group;
use App\Form\EventType;
use App\Helper\UploadFile;
use App\Repository\EventRepository;
use App\Repository\GroupRepository;
use App\Service\EventService;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
final class EventController extends AbstractController
{
    private readonly EventRepository $eventRepository;
    private readonly EntityManagerInterface $em;
    private readonly EventService $eventService;
    private readonly \DateTimeImmutable $now;

    public function __construct(EventRepository $eventRepository, EntityManagerInterface $em, EventService $eventService)
    {
        $this->eventRepository = $eventRepository;
        $this->em = $em;
        $this->eventService = $eventService;
        $this->now = new \DateTimeImmutable();
    }

    #[Route(['/', '/event'], name: 'app_event', methods: ['GET'])]
    public function index(): Response
    {
        // On teste si un utilisateur est connecté
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('success', "Bienvenue {$this->getUser()->getName()}");
        } else {
            $this->addFlash('error', 'Vous avez tenté d\'accéder à une page à laquelle vous n\'avez pas accès. Veuillez vous identifier.');
            return $this->redirectToRoute('app_login');
        }

        // On récupère la liste des événements
        $events = $this->eventRepository->findAll();

        // On initialise nos variables
        $inscriptionsCountById = [];
        $isRegisteredById = [];

        // Pour chaque événement...
        foreach ($events as $event) {
            // On récupère l'ID
            $eventId = $event->getId();
            // On compte le nombre de participants
            $inscriptionsCountById[$eventId] = $event->getParticipants()->count();
            // On teste si l'utilisateur connecté est inscrit
            if ($event->getParticipants()->contains($this->getUser())) {
                $isRegisteredById[$eventId] = true;
            } else {
                $isRegisteredById[$eventId] = false;
            }
            // On teste si la date de clotûre des inscriptions est passée
            if ($event->getInscriptionLimitAt() <= $this->now && $event->getState() !== "CANCELLED") {

                $event->setState('CLOSED');
                $this->em->persist($event);
                $this->em->flush();
            }
            if ($event->getStartAt() <= $this->now && $event->getEndAt() >= $this->now) {
                $event->setState('PROCESSING');
            }
            if ($event->getEndAt() <= $this->now) {
                $event->setState('FINISHED');
            }
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'inscriptionCount' => $inscriptionsCountById,
            'isRegisteredById' => $isRegisteredById,
        ]);
    }

    #[Route('/event/create', name: 'app_event_create')]
    public function create(Request $request, UploadFile $uploadFile): Response
    {
        $event = new Event();
        $groupRepository = $this->em->getRepository(Group::class);
        $groups = $groupRepository->findBy(['creator' => $this->getUser()]);
        $form = $this->createForm(EventType::class, $event, ['groups' => $groups]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setOrganizer($this->getUser());
            if ($form->get('image')->getData()) {
                $file = $form->get('image')->getData();
                $name = $uploadFile->upload($file, $event->getName(), $this->getParameter('kernel.project_dir') . '/public/uploads');
                $event->setPhoto($name);
            }
            $this->em->persist($event);
            $this->em->flush();
            $this->addFlash('success', "La sortie {$event->getName()} a bien été créée");
            return $this->redirectToRoute('app_event_detail', ['id' => $event->getId()]);
        }

        return $this->render('event/create.html.twig', [
            'form' => $form
        ]);
    }

    /**
     * @param Event $event
     * @return Response
     * @throws Exception
     */
    #[Route('/event/detail/{id}', name: 'app_event_detail', requirements: ['id' => '\d+'])]
    public function detail(Event $event): Response
    {
        $inscriptionCount = $event->getParticipants()->count();
        $isRegistered = false;
        if ($event->getParticipants()->contains($this->getUser())) {
            $isRegistered = true;
        }

        // Calculer la date d'inscription limite
        if ($event->getInscriptionLimitAt() >= $this->now) {
            $limitIsPassed = false;
        } else {
            $limitIsPassed = true;
        }

        return $this->render('event/detail.html.twig', [
            'event' => $event,
            'inscriptionCount' => $inscriptionCount,
            'isRegistered' => $isRegistered,
            'limitIsPassed' => $limitIsPassed,
        ]);
    }

    #[Route('/event/update/{id}', name: 'app_event_update', requirements: ['id' => '\d+'])]
    public function update(Event $event, Request $request, UploadFile $uploadFile): Response
    {
        if (!$this->eventService->isEventCreator($event, $this->getUser()) && !$this->isGranted("ROLE_ADMIN")) {
            throw $this->createAccessDeniedException("Modification interdite");
        }
        $readonly =  $this->isGranted("ROLE_ADMIN") && !$this->eventService->isEventCreator($event, $this->getUser());
        $form = $this->createForm(EventType::class, $event,  ['display_isPublish' => false, 'display_isPrivate' => false, 'readonly' =>  $readonly]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('image')->getData()) {
                $file = $form->get('image')->getData();
                $name = $uploadFile->upload($file, $event->getName(), $this->getParameter('kernel.project_dir') . '/public/uploads');
                $event->setPhoto($name);
            }
            $this->em->persist($event);
            $this->em->flush();
            $this->addFlash('success', "La sortie {$event->getName()} a bien été modifié");
            return $this->redirectToRoute('app_event_detail', ['id' => $event->getId()]);
        }

        return $this->render('event/update.html.twig', [
            'event' =>$event,
            'readonly' =>  $readonly,
            'form' => $form,
            'canCancel' => $this->eventService->canCancel($event)
        ]);
    }

    #[Route('/event/delete/{id}', name: 'app_event_delete', requirements: ['id' => '\d+'])]
    public function delete(Event $event): Response
    {
        if (!$this->eventService->isEventCreator($event, $this->getUser()) && !$this->isGranted("ROLE_ADMIN")) {
            throw $this->createAccessDeniedException("Annulation interdite");
        }
        $this->em->remove($event);
        $this->em->flush();
        $this->addFlash('success', "La sortie a bien été supprimé");
        return $this->redirectToRoute('app_event');
    }

    #[Route('/event/publish/{id}', name: 'app_event_publish', requirements: ['id' => '\d+'])]
    public function publish(Event $event): Response
    {
        if (!$this->eventService->isEventCreator($event, $this->getUser()) || $event->isPublished()) {
            throw $this->createAccessDeniedException("Modification interdite");
        }
        $event->setIsPublished(true);
        $event->setState(Event::OPENED);
        $this->em->persist($event);
        $this->em->flush();
        $this->addFlash('success', "La sortie a bien été publié");
        return $this->redirectToRoute('app_event_detail', ['id' => $event->getId()]);
    }

    #[Route('/event/cancel/{id}', name: 'app_event_cancel', requirements: ['id' => '\d+'])]
    public function cancel(Event $event, Request $request): Response
    {
        if (!$this->eventService->isEventCreator($event, $this->getUser()) && !$this->isGranted("ROLE_ADMIN")) {
            throw $this->createAccessDeniedException("Annulation interdite");
        }
        $cancelMessage = $request->query->get("cancelMessage") || "Motif inconnu";

        $event->setState(Event::CANCELLED);
        $event->setCancelMessage($cancelMessage);
        $this->em->persist($event);
        $this->em->flush();
        $this->addFlash('success', "La sortie a bien été annulé");
        return $this->redirectToRoute('app_event_detail', ['id' => $event->getId()]);
    }
    /**
     * 2003 - S'inscrire à un événement
     * @param Event $event
     * @param Request $request
     * @return Response
     */
    #[Route('/event/register/{id}', name: 'app_event_register', requirements: ['id' => '\d+'])]
    public function register(Event $event, Request $request): Response
    {
        if ($event->getParticipants()->contains($this->getUser())) {
            // Ne doit pas arriver puisque le bouton est caché, mais au cas où...
            $this->addFlash('danger', 'Vous êtes déjà inscrit à cet event');
            return $this->redirectToRoute('app_event_detail', ['id' => $event->getId()]);
        }

        if ($event->getMaxParticipant() <= $event->getParticipants()->count()) {
            $this->addFlash('danger', 'Le nombre de participants maximum est déjà atteint');
            return $this->redirectToRoute('app_event_detail', ['id' => $event->getId()]);
        }

        $event->addParticipant($this->getUser());
        $this->em->persist($event);
        $this->em->flush();
        $this->addFlash('success', "Vous êtes maintenant inscrit à l'événement {$event->getName()}");
        return $this->redirectToRoute('app_event_detail', ['id' => $event->getId()]);
    }

    /**
     * 2004 - Se désister d'un événement
     * @param Event $event
     * @param Request $request
     * @return Response
     */
    #[Route('event/unregister/{id}', name: 'app_event_unregister', requirements: ['id' => '\d+'])]
    public function unregister(Event $event, Request $request): Response
    {
        if (!$event->getParticipants()->contains($this->getUser())) {
            // Ne doit pas arriver puisque le bouton est caché, mais au cas où...
            $this->addFlash('danger', 'Vous n\'êtes pas inscrit à cet event');
            return $this->redirectToRoute('app_event_detail', ['id' => $event->getId()]);
        }

        $event->removeParticipant($this->getUser());
        $this->em->persist($event);
        $this->em->flush();
        $this->addFlash('success', "Vous vous êtes désisté de l'événement {$event->getName()}");
        return $this->redirectToRoute('app_event_detail', ['id' => $event->getId()]);
    }
}
