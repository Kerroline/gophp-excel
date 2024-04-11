<?php

namespace Kerroline\PhpGoExcel;

interface GeneratorInterface
{
    public function execute(string $excelFilePath, string $serializedDataPath): void;
}
