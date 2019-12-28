<?php

namespace App\Repository;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param int $id
     *
     * @return User|null
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getNonAdminUserWithSubscription(int $id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('u.id', $id));

        $users = $this->getNonAdminUsersWithSubscription($criteria);

        return (count($users) > 0) ? array_shift($users) : null;
    }

    /**
     * TODO: переделать в подзапрос
     *
     * @param Criteria $criteria
     *
     * @return User[]
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getNonAdminUsersWithSubscription(Criteria $criteria = null)
    {
        $adminIds = $this->createQueryBuilder('u')
            ->select(['u.id'])
            ->leftJoin('u.groups', 'g')
            ->where('g.id = :groupId')
            ->setParameter('groupId', Group::BROWSER_ADMIN)
            ->getQuery()
            ->getArrayResult();

        $innerCriteria = Criteria::create();
        $innerCriteria->where(Criteria::expr()->notIn('u.id', $adminIds));

        if (isset($criteria)) {
            $innerCriteria->andWhere($criteria->getWhereExpression());
        }

        return $this->createQueryBuilder('u')
            ->select(['u', 's'])
            ->leftJoin('u.subscription', 's')
            ->addCriteria($innerCriteria)
            ->getQuery()
            ->getResult();
    }
}
