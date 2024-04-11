<?php

namespace Kerroline\PhpGoExcel;

final class Writer extends BaseWriter
{
    protected function getGeneratorCommandPath(): string
    {
        $commandPath = '';

        $os = php_uname('s');
        $arch = php_uname('m');
        $generatorPath = "{$os}/{$arch}";

        $commandPath = "{$generatorPath}/generator";

        return $commandPath;
    }

    protected function getDataFilePath(): string
    {
        return dirname(__DIR__) . '/data/json_data.json';
    }
}
