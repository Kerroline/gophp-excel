<?php

namespace Kerroline\PhpGoExcel\Interfaces;

interface GeneratorInterface
{
    public function execute(array $data): string;
}
