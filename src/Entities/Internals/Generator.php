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

    public function execute(array $data): string
    {
        $serializedData = json_encode($data);

        chmod($this->commandPath, 0777);

        $descriptorSpec = [
            ["pipe", "r"],  // stdin
            ["pipe", "w"],  // stdout
            ["pipe", "w"],  // stderr
        ];

        $process = proc_open(
            $this->commandPath, 
            $descriptorSpec, 
            $pipes
        );

        $result = '';
        $code = -1;

        if (is_resource($process)) {
            fwrite($pipes[0], $serializedData);
            fclose($pipes[0]);

            $result = stream_get_contents($pipes[1]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            $code = proc_close($process);
        }

        if ($code === 126) {
            throw new \Exception('Command is not executable');
        }

        if ($code === 127) {
            throw new \Exception('Command is not found');
        }

        if ($code !== 0) {
            throw new \Exception($result, $code);
        }

        if ($code === 0) {
            return $result;
        }
    }
}
