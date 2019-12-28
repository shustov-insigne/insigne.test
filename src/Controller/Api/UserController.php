<?php

namespace App\Controller\Api;

use App\Entity\Subscription;
use App\Grid\NonAdminUsersGrid;
use App\Repository\UserRepository;
use App\Validation\UserValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @var UserValidator
     */
    private $validator;


    /**
     * UserController constructor.
     * @param UserValidator $validator
     */
    public function __construct(UserValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @Route("/api/users/grid", name="app_users_grid", methods={"POST"})
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

    /**
     * TODO: разбором json'а и инициализацией объекта $user должен заниматься отдельный класс
     *
     * @Route("/api/user/{id}", name="app_user_update", methods={"PUT"})
     *
     * @param mixed $id
     * @param UserRepository $repository
     * @param Request $request
     * @param DecoderInterface $decode
     * @param UserPasswordEncoderInterface $passwordEncoder
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function update($id, UserRepository $repository, Request $request, DecoderInterface $decode, UserPasswordEncoderInterface $passwordEncoder)
    {
        $id = (int)$id;
        $user = $repository->getNonAdminUserWithSubscription($id);

        if (!isset($user)) {
            return new JsonResponse([
                'result' => 'error',
                'code' => 'object_not_found',
                'description' => "Пользователь с id {$id} не найден",
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        $subscription = $user->getSubscription();
        $dataToUpdate = $decode->decode($request->getContent(), 'json');

        $user->setLogin((string)$dataToUpdate['login'])
            ->setEmail((string)$dataToUpdate['email'])
            ->setLastName((string)$dataToUpdate['lastName'])
            ->setFirstName((string)$dataToUpdate['firstName'])
            ->setSecondName((string)$dataToUpdate['secondName']);

        $password = (string)$dataToUpdate['password'];
        if (mb_strlen($password) > 0) {
            $user->setPassword($passwordEncoder->encodePassword($user, $password));
        }

        $subscriptionDate = (string)$dataToUpdate['subscriptionDate'];
        $dt = \DateTime::createFromFormat('d-m-Y', $subscriptionDate);

        if ($dt) {
            if (!isset($subscription)) {
                $subscription = (new Subscription())->setUser($user);
            }
            $subscription->setDateObject($dt);
            $em->persist($subscription);
        } elseif (isset($subscription)) {
            $em->remove($subscription);
        }

        $error = $this->validator->validateBeforeSave($user);

        if (isset($error)) {
            return new JsonResponse([
                'result' => 'error',
                'code' => $error->getCode(),
                'description' => $error->getDescription()
            ]);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['result' => 'success'], Response::HTTP_OK);
    }
}