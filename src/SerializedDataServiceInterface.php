<?php

namespace Kerroline\PhpGoExcel;

interface SerializedDataServiceInterface
{
    public function saveToFile(array $serializedSpreadsheet): void;
    public function deleteFile(): void;
}
