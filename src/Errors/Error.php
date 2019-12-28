<?php

namespace App\Errors;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class Error implements ErrorInterface
{
    public const UNKNOWN_ERROR = 'unknown_error';
    public const OBJECT_NOT_FOUND = 'object_not_found';
    public const ACCESS_FROBIDDEN = 'access_forbidden';
    public const WRONG_FORMAT = 'wrong_format';


    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $description;


    /**
     * Error constructor.
     * @param string $code
     * @param string $description
     */
    public function __construct(string $code, string $description)
    {
        $this->code = $code;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param null $format
     * @param array $context
     *
     * @return array
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        return [
            'code' => $this->code,
            'description' => $this->description,
        ];
    }
}