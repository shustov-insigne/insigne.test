<?php

namespace App\Controller\PublicScope;

use App\Grid\NonAdminUsersGrid;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="app_users")
     *
     * @param NonAdminUsersGrid $grid
     *
     * @return Response
     */
    public function grid(NonAdminUsersGrid $grid): Response
    {
        return $this->render('user/list.html.twig', [
            'columns' => $grid->getColumns(),
        ]);
    }

    /**
     * @Route("user/{id}", name="app_user")
     *
     * @param mixed $id
     * @param UserRepository $repository
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function detail($id, UserRepository $repository): Response
    {
        $id = (int)$id;
        $user = $repository->getNonAdminUserWithSubscription($id);

        if (!isset($user)) {
            throw $this->createNotFoundException();
        }

        $subscription = $user->getSubscription();

        return $this->render('user/detail.html.twig', [
            'userId' => $user->getId(),
            'login' => $user->getLogin(),
            'email' => $user->getEmail(),
            'lastName' => $user->getLastName(),
            'firstName' => $user->getFirstName(),
            'secondName' => $user->getSecondName(),
            'subscriptionDate' => isset($subscription) ? $subscription->getDateObject()->format('d-m-Y') : ''
        ]);
    }
}