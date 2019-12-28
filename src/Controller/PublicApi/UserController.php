<?php

namespace App\Controller\PublicApi;

use App\Api\ResultType;
use App\Api\Result;
use App\Api\UserToUpdate;
use App\Errors\Error;
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

class UserController extends AbstractController
{
    /**
     * @var UserValidator
     */
    private $validator;

    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var Serializer
     */
    private $serializer;


    /**
     * UserController constructor.
     * @param UserValidator $validator
     */
    public function __construct(UserValidator $validator, UserRepository $repository)
    {
        $this->validator = $validator;
        $this->repository = $repository;
        $this->serializer = new Serializer([new CustomNormalizer(), new ArrayDenormalizer()], ['json' => new JsonEncoder()]);
    }

    /**
     * @Route("/users", name="api_get_all_users", methods={"GET"}, host="api.{domain}.{extension}")
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getAll()
    {
        $result = (new Result(ResultType::READ))
            ->setData($this->repository->getNonAdminUsersWithSubscription());

        return new JsonResponse($this->serializer->serialize($result, 'json'), Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/user/{id}", name="api_get_user", methods={"GET"}, host="api.{domain}.{extension}")
     *
     * @param mixed $id
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getById($id)
    {
        $result = new Result(ResultType::READ);
        $user = $this->repository->getNonAdminUserWithSubscription((int)$id);

        if (!isset($user)) {
            $result->setError(new Error(Error::OBJECT_NOT_FOUND, "Пользователь с id {$id} не найден"));
        } else {
            $result->setData($user);
        }

        return new JsonResponse($this->serializer->serialize($result, 'json'), Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/user/{id}", name="api_update_user", methods={"PUT"})
     *
     * @param mixed $id
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function updateById($id, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $result = new Result(ResultType::UPDATE);
        $user = $this->repository->getNonAdminUserWithSubscription((int)$id);

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

        $error = $this->validator->validateBeforeSave($user);
        if (isset($error)) {
            $result->setError($error);
            return new JsonResponse($this->serializer->serialize($result, 'json'), Response::HTTP_OK, [], true);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse($this->serializer->serialize($result, 'json'), Response::HTTP_OK, [], true);
    }
}