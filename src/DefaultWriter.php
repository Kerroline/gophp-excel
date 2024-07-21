<?php

namespace Kerroline\PhpGoExcel;

use Kerroline\PhpGoExcel\Entities\Internals\BaseWriter;

final class DefaultWriter extends BaseWriter
{
    private const BITS_IN_BYTE = 8;
    private const WINDOWS = 'Win';

    
    protected function getGeneratorCommandPath(): string
    {
        $os = ucfirst(strtolower(substr(PHP_OS, 0, 3))) === self::WINDOWS
            ? self::WINDOWS
            : PHP_OS;

        $osArch = self::BITS_IN_BYTE * PHP_INT_SIZE;

        $arch = "x{$osArch}";

        $osTemplate = "{$os}/{$arch}";

        $commandPath = dirname(__DIR__, 3) .  "/bin/{$osTemplate}/generator";

        return $commandPath;
    }
}
