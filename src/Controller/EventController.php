<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    private readonly EventRepository $eventRepository;
    private readonly EntityManagerInterface $em;
    public function __construct(EventRepository $eventRepository, EntityManagerInterface $em) {
        $this->em = $em;
        $this->eventRepository = $eventRepository;
    }

    #[Route('/', name: 'app_event', methods: ['GET'])]
    public function index(): Response
    {

        return $this->render('event/index.html.twig', [
            'events' => $this->eventRepository->findAll(),
        ]);
    }
}
