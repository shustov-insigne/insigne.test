<?php

namespace App\Security;

use App\Api\Result;
use App\Api\ResultType;
use App\Errors\Error;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;

class HttpBasicAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;


    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {
        $this->em = $em;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @return Serializer
     */
    protected function getSerializer()
    {
        if (!isset($this->serializer)) {
            $this->serializer = new Serializer([new CustomNormalizer(), new ArrayDenormalizer()], ['json' => new JsonEncoder()]);
        }

        return $this->serializer;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        return (strpos($request->getHost(), 'api.') === 0) && ($request->headers->has('Authorization'));
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     *
     * @param Request $request
     *
     * @return array
     */
    public function getCredentials(Request $request)
    {
        $credentials = [
            'login' => '',
            'password' => ''
        ];

        $authInfo = explode(' ', $request->headers->get('Authorization'));

        if ((count($authInfo) === 2) && ($authInfo[0] === 'Basic')) {

            $decoded = base64_decode($authInfo[1]);

            if ($decoded !== false) {
                $exploded = explode(':', $decoded);
                $credentials['login'] = $exploded[0];
                $credentials['password'] = $exploded[1];
            }
        }

        return $credentials;
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return null|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (
            ($credentials['login'] !== $this->parameterBag->get('app.http_basic.login')) ||
            ($credentials['password'] !== $this->parameterBag->get('app.http_basic.password'))
        ) {
            return null;
        }

        /** @var UserRepository $repo */
        $repo = $this->em->getRepository(User::class);

        return $repo->findOneBy(['login' => User::API_USER_LOGIN]);
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     *
     * @return null|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $result = (new Result(ResultType::OTHER))
            ->setError(new Error(Error::ACCESS_FROBIDDEN, 'Неверный логин или пароль'));

        return new JsonResponse($this->getSerializer()->serialize($result, 'json'), Response::HTTP_FORBIDDEN, [], true);
    }

    /**
     * Called when authentication is needed, but it's not sent
     *
     * @param Request $request
     * @param AuthenticationException|null $authException
     *
     * @return JsonResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $result = (new Result(ResultType::OTHER))
            ->setError(new Error(Error::ACCESS_FROBIDDEN, 'Логин и пароль не указаны'));

        return new JsonResponse($this->getSerializer()->serialize($result, 'json'), Response::HTTP_FORBIDDEN, [], true);
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
