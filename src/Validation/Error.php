<?php

namespace App\Validation;

use App\Errors\Error as BaseError;

class Error extends BaseError
{
    public const NOT_UNIQUE_VALUE = 'not_unique_value';
    public const REQUIRED_FIELD_IS_EMPTY = 'required_field_is_empty';
    public const NOT_VALID_FIELD_VALUE = 'not_valid_field_value';
}