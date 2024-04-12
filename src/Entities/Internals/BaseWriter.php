<?php

namespace Kerroline\PhpGoExcel\Entities\Internals;

use Kerroline\PhpGoExcel\Interfaces\{
    GeneratorInterface,
    SerializedDataServiceInterface
};
use Kerroline\PhpGoExcel\Entities\Spreadsheet;

abstract class BaseWriter
{
    abstract protected function getGeneratorCommandPath(): string;
    abstract protected function getDataFilePath(): string;


    public function save(Spreadsheet $spreadsheet, string $filePath): void
    {
        $serializedSpreadsheet = $spreadsheet->serialize();

        $dataService = $this->getDataService();
        $dataService->saveToFile($serializedSpreadsheet);

        $generator = $this->getGenerator();
        $generator->execute($filePath, $this->getDataFilePath());

        $dataService->deleteFile();
    }


    protected function getDataService(): SerializedDataServiceInterface
    {
        return new SerializedDataService($this->getDataFilePath());
    }

    protected function getGenerator(): GeneratorInterface
    {
        return new Generator($this->getGeneratorCommandPath());
    }
}
