<?php

namespace App\Controller\Frontend;

use App\Grid\NonAdminUsersGrid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/users")
 *
 * Class UserController
 * @package App\Controller\Frontend
 */
class UserController extends AbstractController
{
    /**
     * @Route("", name="app_users")
     *
     * @param NonAdminUsersGrid $grid
     *
     * @return Response
     */
    public function index(NonAdminUsersGrid $grid): Response
    {
        return $this->render('user/list.html.twig', [
            'columns' => $grid->getColumns(),
        ]);
    }
}