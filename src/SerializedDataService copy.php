<?php

namespace Kerroline\PhpGoExcel;

final class SerializedDataService implements SerializedDataServiceInterface
{
    /** @var string */
    private $filePath;

    public function getFilePath(): string
    {
    }
    public function setFilePath(string $path): void
    {
        $this->filePath = $path;
    }

    public function saveToFile(array $serializedSpreadsheet): void
    {
        file_put_contents($this->filePath, json_encode($serializedSpreadsheet));
    }

    public function deleteFile(): void
    {
        unlink($this->filePath);
    }
}
