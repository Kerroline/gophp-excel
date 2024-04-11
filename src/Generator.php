<?php

namespace Kerroline\PhpGoExcel;

final class Generator implements GeneratorInterface
{
    /** @var string */
    private $commandPath;

    public function __construct(string $commandPath)
    {
        $this->commandPath = $commandPath;
    }

    public function execute(string $excelFilePath, string $serializedDataPath): void
    {
        chmod($this->commandPath, 0777);

        $res = exec("{$this->commandPath} --filename={$excelFilePath} --dataFilename={$serializedDataPath}", $out, $code);

        if ($code !== 0) {
            throw new \Exception($res, $code);
        }
    }
}
