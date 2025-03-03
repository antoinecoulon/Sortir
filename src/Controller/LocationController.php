<?php

namespace App\Controller;

use App\Entity\Location;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class LocationController extends AbstractController
{
    private readonly LocationRepository $locationRepository;
    private readonly EntityManagerInterface $em;

    public function __construct(LocationRepository $locationRepository, EntityManagerInterface $em)
    {
        $this->locationRepository = $locationRepository;
        $this->em = $em;
    }

    #[Route('/location', name: 'app_location')]
    public function index(): Response
    {
        return $this->render('location/index.html.twig', [
            'controller_name' => 'LocationController',
        ]);
    }

    #[Route('/location/create', name: 'app_location_create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $location = new Location();
        $location->setName($data['name'] ?? '');
        $location->setStreet($data['street'] ?? '');
        $location->setCp($data['cp'] ?? '');
        $location->setStreetNumber((int)$data['streetNumber'] ?? null);
        $location->setCity($data['city'] ?? '');
        $location->setLatitude($data['latitude'] ?? null);
        $location->setLongitude($data['longitude'] ?? null);

        $errors = $validator->validate($location);
        if(count($errors) > 0) {
            return new JsonResponse(['message' => 'Le formulaire n\'est pas valide'], 400);
        }

        $this->em->persist($location);
        $this->em->flush();
        $locationId = $location->getId();
        $locationName = $location->getName();
        return new JsonResponse(['message' => 'success', 'locationId' => $locationId, 'locationName' =>$locationName ]);
    }
}
