<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private const NON_ADMIN_USERS_COUNT = 20;


    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;


    /**
     * AppFixtures constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        // groups

        $adminGroup = (new Group())
            ->setId(Group::BROWSER_ADMIN)
            ->setName(Group::BROWSER_ADMIN);

        $defaultGroup = (new Group())
            ->setId(Group::BROWSER_USER)
            ->setName(Group::BROWSER_USER);

        $apiUserGroup = (new Group())
            ->setId(Group::API_USER)
            ->setName(Group::API_USER);

        $manager->persist($adminGroup);
        $manager->persist($defaultGroup);
        $manager->persist($apiUserGroup);
        $manager->flush();

        // admin

        $admin = (new User())
            ->addGroup($defaultGroup)
            ->addGroup($adminGroup)
            ->setLogin('admin')
            ->setFirstName('Администратор')
            ->setLastName('Сайта');

        $admin->setPassword($this->passwordEncoder->encodePassword($admin, 'admin'));
        $manager->persist($admin);

        // api user

        $apiUser = (new User())
            ->addGroup($apiUserGroup)
            ->addGroup($adminGroup)
            ->setLogin(User::API_USER_LOGIN)
            ->setFirstName('Пользователь')
            ->setLastName('API');

        $manager->persist($apiUser);

        // non admin users

        for ($index = 0; $index < static::NON_ADMIN_USERS_COUNT; ++$index) {

            $firstName = 'user';
            $lastName = "last_name_{$index}";
            $secondName = "second_name_{$index}";
            $login = $firstName . ucfirst($lastName);
            $email = "{$firstName}@{$lastName}.com";

            $user = (new User())
                ->addGroup($defaultGroup)
                ->setLogin($login)
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setSecondName($secondName)
                ->setEmail($email);

            $user->setPassword($this->passwordEncoder->encodePassword($user, $login));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
