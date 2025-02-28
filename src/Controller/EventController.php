<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
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

    public function __construct(EventRepository $eventRepository, EntityManagerInterface $em)
    {
        $this->eventRepository = $eventRepository;
        $this->em = $em;
    }

    #[Route(['/', '/event'], name: 'app_event', methods: ['GET'])]
    public function index(): Response
    {

        // On teste si un utilisateur est connecté
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // Récupère la valeur user.email de l'utilisateur connecté
            $current_user = $this->getUser()->getUserIdentifier();
        } else {
            // NOTE 27/02: théoriquement maintenant l'utilisateur sera toujours connecté avant d'arriver sur cette page !
            // L'utilisateur n'est pas connecté, définir une valeur par défaut
            $current_user = 'Utilisateur non connecté';
            // et le rediriger vers app_login avec un message clair
            // $this->addFlash('error', 'Vous avez tenté d'accéder à une page à laquelle vous n'avez pas accès. Veuillez vous identifier.');
            // return $this->redirectToRoute('app_login');
        }

        // Récupère la liste des events avec le nombre d'inscriptions pour chaque, pas objet!
        $events = $this->eventRepository->findAllEventsWithInscriptionCount();
        // On récupère un tableau associatif qu'on transforme en tableau indexé par l'ID pour faciliter la récupération du compte d'inscrits
        $inscriptionsCountById = [];
        foreach ($events as $count) {
            $inscriptionsCountById[$count['eventId']] = $count['inscriptionCount'];
        }

        // Retourne des objets Event
        $allEvents = $this->eventRepository->findAll();
        $isRegisteredById = [];
        // Pour chaque événement on teste si l'utilisateur courant y est inscrit
        foreach ($allEvents as $event) {
            $eventId = $event->getId();
            if ($event->getParticipants()->contains($this->getUser())) {
                $isRegisteredById[$eventId] = true;
            } else {
                $isRegisteredById[$eventId] = false;
            }
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'inscriptionCount' => $inscriptionsCountById,
            'isRegisteredById' => $isRegisteredById,
            'current_user' => $current_user,
        ]);
    }

    #[Route('/event/create', name: 'app_event_create')]
    public function create(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setOrganizer($this->getUser());
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

        $inscriptionCount = $this->eventRepository->findInscriptionCount($event->getId());

        $isRegistered = false;
        if ($event->getParticipants()->contains($this->getUser())) {
            $isRegistered = true;
        }

        return $this->render('event/detail.html.twig', [
            'event' => $event,
            'inscriptionCount' => $inscriptionCount,
            'isRegistered' => $isRegistered,
        ]);
    }

    #[Route('/event/update/{id}', name: 'app_event_update', requirements: ['id' => '\d+'])]
    public function update(Event $event, Request $request): Response
    {
        $form = $this->createForm(EventType::class, $event, ['display_isPublish' => false]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($event);
            $this->em->flush();
            $this->addFlash('success', "La sortie {$event->getName()} a bien été modifié");
            return $this->redirectToRoute('app_event_detail', ['id' => $event->getId()]);
        }

        return $this->render('event/update.html.twig', [
            'form' => $form,
            'isPublished' => $event->isPublished(),
            'id' => $event->getId()
        ]);
    }

    #[Route('/event/delete/{id}', name: 'app_event_delete', requirements: ['id' => '\d+'])]
    public function delete(Event $event): Response
    {
        $this->addFlash('success', "La sortie a bien été supprimé");
        return $this->redirectToRoute('app_event');
    }

    #[Route('/event/publish/{id}', name: 'app_event_publish', requirements: ['id' => '\d+'])]
    public function publish(Event $event): Response
    {
        if ($event->isPublished() || !$event->getOrganizer() || $event->getOrganizer()->getId() !== $this->getUser()->getId()) {
            return throw new AccessDeniedException("Action interdite");
        }
        $event->setIsPublished(true);
        $event->setState(Event::OPENED);
        $this->em->persist($event);
        $this->em->flush();
        $this->addFlash('success', "La sortie a bien été publié");
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
        $event->removeParticipant($this->getUser());
        $this->em->persist($event);
        $this->em->flush();
        $this->addFlash('success', "Vous vous êtes désisté de l'événement {$event->getName()}");
        return $this->redirectToRoute('app_event_detail', ['id' => $event->getId()]);
    }
}
