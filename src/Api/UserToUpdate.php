<?php

namespace App\Api;

use App\Entity\Subscription;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManagerInterface;

class UserToUpdate implements DenormalizableInterface
{
    /**
     * TODO: если классов, аналогичных UserToUpdate, будет больше, то необходимо создать отдельный слой "полей"
     *
     * @var array
     */
    private static $fieldMap = [
        'login' => ['type' => 'string'],
        'email' => ['type' => 'string'],
        'lastName' => ['type' => 'string'],
        'firstName' => ['type' => 'string'],
        'secondName' => ['type' => 'string'],
        'password' => ['type' => 'string'],
        'subscriptionDate' => ['type' => 'datetime', 'format' => 'd-m-Y'],
    ];

    /**
     * @var array
     */
    private $values = [];


    /**
     * @param DenormalizerInterface $denormalizer
     * @param array $data
     * @param null $format
     * @param array $context
     * @return void
     */
    public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = [])
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('$data is expected to be an array');
        }

        foreach (static::$fieldMap as $fieldName => $fieldInfo) {

            if (!array_key_exists($fieldName, $data)) {
                continue;
            }

            switch ($fieldInfo['type']) {
                case 'string':
                    $this->values[$fieldName] = (string)$data[$fieldName];
                    break;
                case 'datetime':
                    $this->values[$fieldName] = \DateTime::createFromFormat($fieldInfo['format'], $data[$fieldName]) ?: null;
                    break;
            }
        }
    }

    /**
     * @param string $fieldName
     * @return mixed|null
     */
    public function get(string $fieldName)
    {
        return $this->values[$fieldName] ?? null;
    }

    /**
     * @param User $user
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $em
     *
     * @return $this
     */
    public function initEntityObject(User $user, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        if (array_key_exists('login', $this->values)) {
            $user->setLogin($this->values['login']);
        }

        if (array_key_exists('email', $this->values)) {
            $user->setEmail($this->values['email']);
        }

        if (array_key_exists('lastName', $this->values)) {
            $user->setLastName($this->values['lastName']);
        }

        if (array_key_exists('firstName', $this->values)) {
            $user->setFirstName($this->values['firstName']);
        }

        if (array_key_exists('secondName', $this->values)) {
            $user->setSecondName($this->values['secondName']);
        }

        if (array_key_exists('password', $this->values) && mb_strlen($this->values['password']) > 0) {
            $user->setPassword($encoder->encodePassword($user, $this->values['password']));
        }

        if (array_key_exists('subscriptionDate', $this->values)) {

            $subscription = $user->getSubscription();

            if ($this->values['subscriptionDate']) {
                if (!isset($subscription)) {
                    $subscription = (new Subscription())->setUser($user);
                }
                $subscription->setDateObject($this->values['subscriptionDate']);
                $em->persist($subscription);
            } elseif (isset($subscription)) {
                $em->remove($subscription);
            }
        }

        return $this;
    }
}