<?php

namespace App\Validation;

use App\Entity\User;
use App\Repository\UserRepository;

class UserValidator implements ValidatorInterface
{
    /**
     * @var UserRepository
     */
    private $repository;


    /**
     * UserValidator constructor.
     * @param UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param User $user
     *
     * @return Error|null
     */
    public function validateBeforeSave($user)
    {
        if (mb_strlen($user->getLogin()) === 0) {
            return new Error(Error::REQUIRED_FIELD_IS_EMPTY, 'Логин не может быть пустым');
        }

        if (mb_strlen($user->getLogin()) === 0) {
            return new Error(Error::REQUIRED_FIELD_IS_EMPTY, 'Пароль не может быть пустым');
        }

        if (!preg_match("/^[A-Za-z0-9]+$/", $user->getLogin())) {
            return new Error(Error::NOT_VALID_FIELD_VALUE, 'Логин должен состоять из латинских символов и цифр');
        }

        if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL) === false) {
            return new Error(Error::NOT_VALID_FIELD_VALUE, 'Некорректный email');
        }

        $firstNameLength = mb_strlen($user->getFirstName());
        $secondNameLength = mb_strlen($user->getSecondName());
        $lastNameLength = mb_strlen($user->getLastName());

        if (
            ($firstNameLength === 0 || $secondNameLength === 0 || $lastNameLength === 0) &&
            ($firstNameLength > 0 || $secondNameLength > 0 || $lastNameLength > 0)
        ) {
            return new Error(Error::NOT_VALID_FIELD_VALUE, 'ФИО должно быть заполнено полностью или не должно быть заполнено совсем');
        }

        /** @var User $retrievedUser */
        $retrievedUser = $this->repository->findOneBy(['login' => $user->getLogin()]);

        if (isset($retrievedUser) && $retrievedUser->getId() !== $user->getId()) {
            return new Error(Error::NOT_UNIQUE_VALUE, 'Указанный логин уже используется');
        }

        return null;
    }

    /*
     * {@inheritdoc}
     */
    public function validateBeforeDelete($object)
    {
        return null;
    }
}