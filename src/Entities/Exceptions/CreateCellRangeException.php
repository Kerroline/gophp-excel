<?php

namespace Kerroline\PhpGoExcel\Entities\Exceptions;

use Exception;

class CreateCellRangeException extends Exception
{
    public function __construct(string $message) {
        parent::__construct($message, 0, null);
    }
}