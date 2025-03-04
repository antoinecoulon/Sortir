<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Unique;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PSEUDO', fields: ['pseudo'])]
#[UniqueEntity(fields: ['email'], message: 'Cet email existe déjà !')]
#[UniqueEntity(fields: ['pseudo'], message: 'Ce pseudo existe déjà !')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le pseudo doit être renseigné')]
    #[Assert\Length(min: 3, max: 20,
        normalizer: 'trim',
        minMessage: 'Trop court ! Au moins {{ limit }} caractères.',
        maxMessage: 'Trop long ! Maximum {{ limit }} caractères')]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_-]+$/i',
        message: 'Veuillez n\'utiliser que des lettres, des chiffres, des underscores et des tirets'
    )]
    private ?string $pseudo = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'L\'email doit être renseigné')]
    #[Assert\Email(
        message: 'Veuillez rentrer un email valide',
        normalizer: 'trim')]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9._%+-]+@campus-eni\.fr$/',
        message: "Veuillez renseigner une adresse email de type @campus-eni@.fr"
    )]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(
        message: 'Le nom ne peut pas être vide',
        normalizer: 'trim')]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(
        message: 'Le prénom ne peut pas être vide',
        normalizer: 'trim')]
    private ?string $firstname = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone doit être renseigné')]
    #[Assert\Length(min: 10, max: 10,
        normalizer: 'trim',
        minMessage: 'Trop court ! Au moins {{ limit }} caractères.',
        maxMessage: 'Trop long ! Maximum {{ limit }} caractères.')]
    private ?string $phone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants')]
    private Collection $events;

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'organizer')]
    private Collection $userEvents;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $site = null;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\OneToMany(targetEntity: Group::class, mappedBy: 'creator')]
    private Collection $privateGroups;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->privateGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

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

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
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
            $event->addParticipant($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            $event->removeParticipant($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getUserEvents(): Collection
    {
        return $this->userEvents;
    }

    public function addUserEvent(Event $event): static
    {
        if (!$this->userEvents->contains($event)) {
            $this->userEvents->add($event);
            $event->addParticipant($this);
        }

        return $this;
    }

    public function removeUserEvent(Event $event): static
    {
        if ($this->userEvents->removeElement($event)) {
            $event->removeParticipant($this);
        }

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

    public function isVerified(): bool
    {
        return $this->isVerified;
    }


    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getPrivateGroups(): Collection
    {
        return $this->privateGroups;
    }

    public function addPrivateGroup(Group $privateGroup): static
    {
        if (!$this->privateGroups->contains($privateGroup)) {
            $this->privateGroups->add($privateGroup);
            $privateGroup->setCreator($this);
        }

        return $this;
    }

    public function removePrivateGroup(Group $privateGroup): static
    {
        if ($this->privateGroups->removeElement($privateGroup)) {
            // set the owning side to null (unless already changed)
            if ($privateGroup->getCreator() === $this) {
                $privateGroup->setCreator(null);
            }
        }

        return $this;
    }

}
