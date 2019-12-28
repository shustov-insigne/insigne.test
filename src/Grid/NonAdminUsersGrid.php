<?php

namespace App\Grid;

use App\Entity\Group;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\ParameterBag;

class NonAdminUsersGrid
{
    /**
     * @var array
     */
    private $columns;

    /**
     * @var string
     */
    private $noDataStub = '--';

    /**
     * @var string
     */
    private $dateFormat = 'd-m-Y';

    /**
     * @var UserRepository
     */
    private $repository;


    /**
     * NonAdminUserGrid constructor.
     * @param UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;

        $this->columns = [
            [
                'data' => 'id', // "field name" для DataTable
                'title' => 'ID',
                'searchable' => 'Y',
                'orderable' => 'Y',
            ],
            [
                'data' => 'login', // "field name" для DataTable
                'title' => 'Логин',
                'searchable' => 'Y',
                'orderable' => 'Y',
            ],
            [
                'data' => 'fullName', // "field name" для DataTable
                'title' => 'ФИО',
                'searchable' => 'Y',
                'orderable' => 'Y',
            ],
            [
                'data' => 'email', // "field name" для DataTable
                'title' => 'Email',
                'searchable' => 'Y',
                'orderable' => 'Y',
            ],
            [
                'data' => 'date', // "field name" для DataTable
                'title' => 'Окончание подписки',
                'searchable' => 'N',
                'orderable' => 'Y',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * https://datatables.net/manual/server-side
     *
     * @param ParameterBag $request
     * @return array
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function processRequest(ParameterBag $request)
    {
        $defaultCriteria = $this->makeDefaultCriteria();
        $complexCriteria = $this->makeComplexCriteria($request, clone $defaultCriteria);

        $recordsTotal = $this->getObjectsCount($defaultCriteria);
        $recordsFiltered = $this->getObjectsCount($complexCriteria);

        $objects = $this->getObjects($complexCriteria, $request);

        return $this->formatObjects($objects, $request->getInt('draw'), $recordsTotal, $recordsFiltered);
    }

    /**
     * @param Criteria $criteria
     * @param ParameterBag $request
     * @return User[]
     * @throws \Doctrine\ORM\Query\QueryException
     */
    private function getObjects(Criteria $criteria, ParameterBag $request)
    {
        $qb = $this->repository->createQueryBuilder('u')
            ->select(['u'])
            ->addCriteria($criteria)
            ->setMaxResults($request->getInt('length'))
            ->setFirstResult($request->getInt('start'));

        $orderItems = $request->get('order');

        if (is_array($orderItems)) {

            foreach ($orderItems as $orderItem) {

                $columnIndex = $orderItem['column'];
                $direction = strtoupper($orderItem['dir']);

                if (in_array($direction, ['ASC', 'DESC'], true)) {

                    $column = $this->columns[$columnIndex];

                    if (isset($column) && ($column['orderable'] === 'Y')) {
                        $qb->addOrderBy("u.{$column['data']}", $direction);
                    }
                }
            }
        }

        /** @var User[] $users */
        $users = $qb->getQuery()
            ->getResult();

        return $users;
    }

    /**
     * @param Criteria $criteria
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\Query\QueryException
     */
    private function getObjectsCount(Criteria $criteria)
    {
        $count = $this->repository->createQueryBuilder('u')
            ->select("COUNT(DISTINCT (u.id))")
            ->addCriteria($criteria)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$count;
    }

    /**
     * @return Criteria
     */
    private function makeDefaultCriteria()
    {
        $adminsIds = $this->repository->createQueryBuilder('u')
            ->select(['u.id'])
            ->join('u.groups', 'g')
            ->where('g.id = :groupId')
            ->setParameter('groupId', Group::BROWSER_ADMIN)
            ->getQuery()
            ->getArrayResult();

        $criteria = Criteria::create();

        if (count($adminsIds) > 0) {
            $criteria->andWhere(Criteria::expr()->notIn('u.id', $adminsIds));
        }

        return $criteria;
    }

    /**
     * @param ParameterBag $request
     * @param Criteria $criteria
     * @return Criteria
     */
    private function makeComplexCriteria(ParameterBag $request, Criteria $criteria)
    {
        $search = $request->get('customSearch');
        if (!$search) {
            return $criteria;
        }

        $id = (int) $search['id'];
        if ($id > 0) {
            $criteria->andWhere(Criteria::expr()->eq("u.id", $id));
        }

        if (mb_strlen($search['login'])) {
            $criteria->andWhere(Criteria::expr()->contains("u.login", $search['login']));
        }

        if (mb_strlen($search['fullName'])) {
            $criteria->andWhere(Criteria::expr()->contains("u.fullName", $search['fullName']));
        }

        if (mb_strlen($search['email'])) {
            $criteria->andWhere(Criteria::expr()->contains("u.email", $search['email']));
        }

        return $criteria;
    }

    /**
     * @param User[] $users
     * @param int $draw
     * @param int $recordsTotal
     * @param int $recordsFiltered
     * @return array
     */
    private function formatObjects(array $users, int $draw, int $recordsTotal, int $recordsFiltered)
    {
        $result = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => []
        ];

        foreach ($users as $user) {

            $subscription = $user->getSubscription();

            $result['data'][] = [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'fullName' => $user->getFullName() ?: $this->noDataStub,
                'email' => $user->getEmail() ?: $this->noDataStub,
                'date' => isset($subscription) ? ($subscription->getDateObject()->format($this->dateFormat)) : $this->noDataStub,
            ];
        }

        return $result;
    }
}