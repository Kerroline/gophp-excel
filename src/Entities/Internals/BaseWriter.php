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


    public final function save(Spreadsheet $spreadsheet, string $filePath): void
    {
        $serializedSpreadsheet = $spreadsheet->serialize();

        $dataService = $this->getDataService();
        $dataService->saveToFile($serializedSpreadsheet);

        $generator = $this->getGenerator();
        $generator->execute($filePath, $this->getDataFilePath());

        $dataService->deleteFile();
    }


    protected final function getDataService(): SerializedDataServiceInterface
    {
        return new SerializedDataService($this->getDataFilePath());
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

    protected function getDataFilePath(): string
    {
        return dirname(__DIR__, 3) . '/data/json_data.json';
    }
}
