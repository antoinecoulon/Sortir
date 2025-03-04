<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $Users;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'privateGroup')]
    private Collection $Events;

    #[ORM\ManyToOne(inversedBy: 'privateGroups')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?user $creator = null;

    public function __construct()
    {
        $this->Users = new ArrayCollection();
        $this->Events = new ArrayCollection();
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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->Users;
    }

    public function addUser(User $user): static
    {
        if (!$this->Users->contains($user)) {
            $this->Users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->Users->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->Events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->Events->contains($event)) {
            $this->Events->add($event);
            $event->setPrivateGroup($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->Events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getPrivateGroup() === $this) {
                $event->setPrivateGroup(null);
            }
        }

        return $this;
    }

    public function getCreator(): ?user
    {
        return $this->creator;
    }

    public function setCreator(?user $creator): static
    {
        $this->creator = $creator;

        return $this;
    }
}
