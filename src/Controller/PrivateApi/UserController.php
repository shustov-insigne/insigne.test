<?php

namespace App\Controller\PrivateApi;

use App\Api\ResultType;
use App\Api\Result;
use App\Api\UserToUpdate;
use App\Errors\Error;
use App\Grid\NonAdminUsersGrid;
use App\Repository\UserRepository;
use App\Validation\UserValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 *
 * Class UserController
 * @package App\Controller\PrivateApi
 */
class UserController extends AbstractController
{
    /**
     * @var Serializer
     */
    private $serializer;


    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->serializer = new Serializer([new CustomNormalizer(), new ArrayDenormalizer()], ['json' => new JsonEncoder()]);
    }

    /**
     * @Route("/users/grid", name="app_users_grid", methods={"POST"})
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
     * @Route("/user/{id}", name="app_user_update", methods={"PUT"})
     *
     * @param mixed $id
     * @param Request $request
     * @param UserRepository $repository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param UserValidator $validator
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function updateById($id, Request $request, UserRepository $repository, UserPasswordEncoderInterface $passwordEncoder, UserValidator $validator)
    {
        $result = new Result(ResultType::UPDATE);
        $user = $repository->getNonAdminUserWithSubscription((int)$id);

        if (!isset($user)) {
            $result->setError(new Error(Error::OBJECT_NOT_FOUND, "Пользователь с id {$id} не найден"));
            return new JsonResponse($this->serializer->serialize($result, 'json'), Response::HTTP_OK, [], true);
        }

        /** @var UserToUpdate|null $resource */
        $resource = null;
        try {
            $resource = $this->serializer->deserialize($request->getContent(), UserToUpdate::class, 'json');
        } catch (SerializerExceptionInterface $e) {}

        if (!($resource instanceof UserToUpdate)) {
            $result->setError(new Error(Error::WRONG_FORMAT, 'Некорректный формат'));
            return new JsonResponse($this->serializer->serialize($result, 'json'), Response::HTTP_OK, [], true);
        }

        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $resource->initEntityObject($user, $passwordEncoder, $em);

        $error = $validator->validateBeforeSave($user);
        if (isset($error)) {
            $result->setError($error);
            return new JsonResponse($this->serializer->serialize($result, 'json'), Response::HTTP_OK, [], true);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse($this->serializer->serialize($result, 'json'), Response::HTTP_OK, [], true);
    }
}