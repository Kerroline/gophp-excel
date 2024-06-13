<?php

namespace Kerroline\PhpGoExcel\Entities\Internals;

use Kerroline\PhpGoExcel\Interfaces\{
    GeneratorInterface,
    SerializedDataServiceInterface
};
use Kerroline\PhpGoExcel\Entities\Spreadsheet;

abstract class BaseWriter
{
    private const BITS_IN_BYTE = 8;
    private const WINDOWS = 'Win';

    public final function saveAsString(Spreadsheet $spreadsheet, string $filePath): string 
    {
        return $this->save($spreadsheet, $filePath, true);
    }

    public final function saveAsFile(Spreadsheet $spreadsheet, string $filePath): void 
    {
        $this->save($spreadsheet, $filePath);
    }

    private final function save(Spreadsheet $spreadsheet, string $filePath, bool $asString = false): string 
    {
        $data = [
            'spreadsheet' => $spreadsheet->serialize(),
            'filename' => $filePath,
            'asString' => $asString,
        ];

        $generator = $this->getGenerator();

        $result = $generator->execute($data);

        return $result;
    }

    protected final function getGenerator(): GeneratorInterface
    {
        return new Generator($this->getGeneratorCommandPath());
    }

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
