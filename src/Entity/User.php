<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="app_user")
 */
class User implements UserInterface, NormalizableInterface
{
    public const API_USER_LOGIN = 'api_user';


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $login;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $secondName;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $fullName;

    /**
     * @var Subscription|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Subscription", mappedBy="user")
     */
    private $subscription;

    /**
     * @var Collection|Group[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Group", inversedBy="users")
     * @ORM\JoinTable(name="app_user_group")
     */
    private $groups;


    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string $login
     * @return User
     */
    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     * @return string
     */
    public function getUsername(): ?string
    {
        return (string) $this->login;
    }

    /**
     * @see UserInterface
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = [];

        foreach ($this->groups as $group) {
            $roles[] = 'ROLE_' . $group->getId();
        }

        return $roles;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        foreach ($this->groups as $group) {
            if ($group->getId() === Group::BROWSER_ADMIN) {
                return true;
            }
        }

        return false;
    }

    /**
     * @see UserInterface
     * @return string
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return null|string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param null|string $firstName
     * @return User
     */
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        $this->resetFullName();

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSecondName(): ?string
    {
        return $this->secondName;
    }

    /**
     * @param null|string $secondName
     * @return User
     */
    public function setSecondName(?string $secondName): self
    {
        $this->secondName = $secondName;
        $this->resetFullName();

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param null|string $lastName
     * @return User
     */
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        $this->resetFullName();

        return $this;
    }

    /**
     * @return User
     */
    private function resetFullName(): self
    {
        $pieces = [];

        if ($this->lastName) {
            $pieces[] = $this->lastName;
        }

        if ($this->firstName) {
            $pieces[] = $this->firstName;
        }

        if ($this->secondName) {
            $pieces[] = $this->secondName;
        }

        $this->fullName = implode(' ', $pieces);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    /**
     * @return Subscription|null
     */
    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    /**
     * @param Subscription $subscription
     * @return User
     */
    public function setSubscription(Subscription $subscription): self
    {
        $this->subscription = $subscription;

        if ($this !== $subscription->getUser()) {
            $subscription->setUser($this);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|Group[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * @param Group $group
     * @return User
     */
    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    /**
     * @param Group $group
     * @return User
     */
    public function removeGroup(Group $group): self
    {
        $key = $this->groups->indexOf($group);
        if ($key !== false) {
            $this->groups->remove($key);
        }

        return $this;
    }

    /**
     * @return User
     */
    public function clearGroups(): self
    {
        $this->groups->clear();

        return $this;
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param null $format
     * @param array $context
     *
     * @return array
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'fullName' => $this->fullName,
            'endOfSubscriptionDate' => is_null($this->subscription) ? '' : $this->subscription->getDateObject()->format('d-m-Y'),
        ];
    }
}
