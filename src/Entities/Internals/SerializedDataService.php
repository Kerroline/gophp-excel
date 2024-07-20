<?php

namespace Kerroline\PhpGoExcel\Entities\Internals;

use Kerroline\PhpGoExcel\Interfaces\SerializedDataServiceInterface;

final class SerializedDataService implements SerializedDataServiceInterface
{
    /** @var string */
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function saveToFile(array $data): void
    {
        file_put_contents($this->filePath, json_encode($data));
    }

    public function deleteFile(): void
    {
        unlink($this->filePath);
    }
}