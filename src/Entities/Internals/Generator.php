<?php

namespace Kerroline\PhpGoExcel\Entities\Internals;

use Kerroline\PhpGoExcel\Interfaces\GeneratorInterface;

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

        $command = "{$this->commandPath} --filename={$excelFilePath} --dataFilename={$serializedDataPath}";

        $res = exec($command, $out, $code);

        if ($code === 126) {
            throw new \Exception('Command is not executable');
        }

        if ($code === 127) {
            throw new \Exception('Command is not found');
        }

        if ($code !== 0) {
            throw new \Exception($res, $code);
        }
    }
}
