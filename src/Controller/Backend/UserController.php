<?php

namespace App\Controller\Backend;

use App\Grid\NonAdminUsersGrid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/users")
 *
 * Class UserController
 * @package App\Controller\Backend
 */
class UserController extends AbstractController
{
    /**
     * @Route("/grid", name="app_users_grid", methods={"POST"})
     *
     * @param Request $httpRequest
     * @param NonAdminUsersGrid $grid
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function grid(Request $httpRequest, NonAdminUsersGrid $grid): Response
    {
        return new JsonResponse($grid->processRequest($httpRequest->request));
    }
}