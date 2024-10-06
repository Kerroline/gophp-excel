<?php

namespace Kerroline\PhpGoExcel\Entities\Exceptions;

use Exception;
use Throwable;

//Refactor: rename class
class Locator 
{
    private const SUCCESS_CODE = 0;
    private const SYNTAXES_ERROR_CODE = 2;
    private const READ_FILE_ERROR_CODE = 11;
    private const CREATE_CELL_RANGE_ERROR_CODE = 14;

    private static function throwError(int $code): Throwable
    {
        $exceptionsMap = [
            self::SYNTAXES_ERROR_CODE => SyntaxesException::class,
            self::READ_FILE_ERROR_CODE => ReadDataFileException::class,
            self::CREATE_CELL_RANGE_ERROR_CODE => CreateCellRangeException::class,
        ];

        $exception = $exceptionsMap[$code] ?? null;

        if (!$exception) {
            throw new Exception("undefined error code: $code");
        }

        throw new $exception;
    }
}
