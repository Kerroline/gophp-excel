<?php

namespace Kerroline\PhpGoExcel;

final class SerializedDataService implements SerializedDataServiceInterface
{
    /** @var string */
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
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
