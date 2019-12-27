<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 * @ORM\Table(name="app_group")
 */
class Group
{
	const BROWSER_USER = 'BROWSER_USER';
	const BROWSER_ADMIN = 'BROWSER_ADMIN';

	/**
	 * @ORM\Id()
	 * @ORM\Column(type="string", length=255)
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $name;

	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="groups")
	 */
	private $users;


	/**
	 * Group constructor.
	 */
	public function __construct()
	{
		$this->users = new ArrayCollection();
	}

	/**
	 * @return null|string
	 */
	public function getId(): ?string
	{
		return $this->id;
	}

	/**
	 * @return null|string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

    /**
     * @param null|string $id
     * @return Group
     */
	public function setId(?string $id): self
	{
		$this->id = $id;

		return $this;
	}

    /**
     * @param null|string $name
     * @return Group
     */
	public function setName(?string $name): self
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return ArrayCollection|User[]
	 */
	public function getUsers(): Collection
	{
		return $this->users;
	}

    /**
     * @param User $user
     * @return Group
     */
	public function addUser(User $user): self
	{
		if (!$this->users->contains($user)) {
			$this->users[] = $user;
			$user->addGroup($this);
		}

		return $this;
	}

    /**
     * @param User $user
     * @return Group
     */
	public function removeUser(User $user): self
	{
		$key = $this->users->indexOf($user);

		if ($key !== false) {
			$this->users->remove($key);
			$user->removeGroup($this);
		}

		return $this;
	}
}
