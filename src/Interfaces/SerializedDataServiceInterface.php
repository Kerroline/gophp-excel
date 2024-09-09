<?php

namespace Kerroline\PhpGoExcel\Interfaces;

interface SerializedDataServiceInterface
{
    public function saveToFile(array $data): void;
    public function deleteFile(): void;
}