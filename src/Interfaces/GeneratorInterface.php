<?php

namespace Kerroline\PhpGoExcel\Interfaces;

interface GeneratorInterface
{
    public function execute(string $excelFilePath, string $serializedDataPath): void;
}
