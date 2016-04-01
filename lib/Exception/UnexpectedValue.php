<?php
namespace FMUP\Exception;

use FMUP\Exception;

/**
 * Class UnexpectedValue
 * @package FMUP\Exception
 */
class UnexpectedValue extends Exception
{
    const CODE_VALUE_EMPTY = 1;
    const CODE_VALUE_NULL = 2;
    const CODE_VALUE_INVALID_FILE_PATH = 3;
    const CODE_TYPE_NOT_STRING = 4;

    const MESSAGE_VALUE_EMPTY = 'Unexpected value of variable, the parameter can not be empty.';
    const MESSAGE_VALUE_NULL = 'Unexpected value of variable, the parameter can not be NULL.';
    const MESSAGE_TYPE_NOT_STRING = 'Unexpected type of variable, the parameter must be a string.';
}
