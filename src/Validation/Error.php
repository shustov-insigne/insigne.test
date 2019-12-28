<?php

namespace App\Validation;

class Error
{
    public const NOT_UNIQUE_VALUE = 'not_unique_value';
    public const REQUIRED_FIELD_IS_EMPTY = 'required_field_is_empty';
    public const NOT_VALID_FIELD_VALUE = 'not_valid_field_value';

    public const UNKNOWN_ERROR = 'unknown_error';


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
}