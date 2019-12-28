<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionRepository")
 * @ORM\Table(name="app_subscription")
 */
class Subscription
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $date;

    /**
     * @var \DateTime
     */
    private $dateObject;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="subscription")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getDateTimestamp(): ?int
    {
        return $this->date;
    }

    /**
     * @param bool $reInit
     * @return \DateTime
     */
    public function getDateObject($reInit = false): \DateTime
    {
        if (!isset($this->dateObject) || $reInit) {
            $this->dateObject = new \DateTime();
            $this->dateObject->setTimestamp($this->date);
        }

        return $this->dateObject;
    }

    /**
     * @param \DateTime $dt
     * @return Subscription
     */
    public function setDateObject(\DateTime $dt): self
    {
        $dt->setTime(23, 59, 59);
        $this->dateObject = $dt;
        $this->date = $dt->getTimestamp();

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Subscription
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
