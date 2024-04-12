<?php

namespace Kerroline\PhpGoExcel\Interfaces;

interface SerializedDataServiceInterface
{
    public function saveToFile(array $serializedSpreadsheet): void;
    public function deleteFile(): void;
}
