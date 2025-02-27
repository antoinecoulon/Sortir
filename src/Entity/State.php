<?php

namespace App\Entity;

use App\Repository\StateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StateRepository::class)]
class State
{

    public const CREATED = 'CREATED';
    public const OPENED = 'OPENED';
    public const CLOSED = 'CLOSED';
    public const PROCESSING = 'PROCESSING';
    public const FINISHED = 'FINISHED';
    public const CANCELLED = 'CANCELLED';

    public const VALID_STATES = [
        self::CREATED,
        self::OPENED,
        self::CLOSED,
        self::PROCESSING,
        self::FINISHED,
        self::CANCELLED
    ];

    public const STATE_LABELS = [
        self::CREATED => 'créée',
        self::OPENED => 'ouverte',
        self::CLOSED => 'cloturée',
        self::PROCESSING => 'cours',
        self::FINISHED => 'passée',
        self::CANCELLED => 'annulée'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'state')]
    private Collection $events;

    public function __construct($name)
    {
        $this->setName($name);
        $this->events = new ArrayCollection();
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
        if (!in_array($name, self::VALID_STATES)) {
            throw new \InvalidArgumentException(sprintf(
                'État invalide "%s". Les états valides sont: %s',
                $name,
                implode(', ', self::VALID_STATES)
            ));
        }
        $this->name = $name;
        return $this;
    }

    public function getStateLabel(): ?string
    {
        if ($this->name === null) {
            return null;
        }

        return self::STATE_LABELS[$this->name] ?? 'État inconnu';
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setState($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getState() === $this) {
                $event->setState(null);
            }
        }

        return $this;
    }
}
