<?php

namespace App\Errors;

use Symfony\Component\Serializer\Normalizer\NormalizableInterface;

interface ErrorInterface extends NormalizableInterface
{
    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return string
     */
    public function getDescription(): string;
}