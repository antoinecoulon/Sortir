<?php

namespace App\Entity;

use App\Enum\EventState;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le nom doit être renseigné')]
    private ?string $name = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le nombre de participants doit être renseigné')]
    #[Assert\Range(
        notInRangeMessage: 'Le nombre de participants doit être entre 1 et 100',
        min: 1,
        max: 100
    )]
    private ?int $maxParticipant = null;

    #[ORM\Column]
    private ?bool $isPublished = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column]
    #[Assert\GreaterThan('+1 hours', message: 'La date de début doit être supérieur à aujourd\'hui')]
    #[Assert\Type(type: \DateTimeImmutable::class, message: 'La date de début doit être renseigné')]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column]
    #[Assert\GreaterThan(propertyPath: 'startAt', message: 'La date de fin doit être supérieur à la date de début')]
    #[Assert\Type(type: \DateTimeImmutable::class, message: 'La date de fin doit être renseigné')]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\Column]
    #[Assert\LessThan(propertyPath: 'startAt', message: 'La date de fin d\'inscription doit être inférieur à la date de début')]
    #[Assert\Type(type: \DateTimeImmutable::class, message: 'La date de fin d\'inscription doit être renseigné')]
    private ?\DateTimeImmutable $inscriptionLimitAt = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $organizer = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le site doit être renseigné')]
    private ?Site $site = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le lieu de l'évenement doit être renseigné")]
    private ?Location $location = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'events')]
    private Collection $participants;

    #[ORM\Column(type: 'event_state')]
    private ?EventState $state = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getMaxParticipant(): ?int
    {
        return $this->maxParticipant;
    }

    public function setMaxParticipant(int $maxParticipant): static
    {
        $this->maxParticipant = $maxParticipant;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getInscriptionLimitAt(): ?\DateTimeImmutable
    {
        return $this->inscriptionLimitAt;
    }

    public function setInscriptionLimitAt(\DateTimeImmutable $inscriptionLimitAt): static
    {
        $this->inscriptionLimitAt = $inscriptionLimitAt;

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getState(): ?EventState
    {
        return $this->state;
    }

    public function setState(EventState $state): static
    {
        $this->state = $state;
        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->setState(EventState::CREATED);
    }
}
