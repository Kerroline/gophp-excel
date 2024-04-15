<?php

namespace Kerroline\PhpGoExcel;

use Kerroline\PhpGoExcel\Entities\Internals\BaseWriter;

final class Writer extends BaseWriter
{
    protected function getGeneratorCommandPath(): string
    {
        /** 
         * TODO: Обдумать сокращение архитектуры до 32/64
         * Как сюда врезать винду
         */
        $commandPath = '';

        $os = php_uname('s');
        $arch = php_uname('m');
        $osTemplate = "{$os}/{$arch}";

        $commandPath = dirname(__DIR__) .  "/bin/{$osTemplate}/generator";

        return $commandPath;
    }

    protected function getDataFilePath(): string
    {
        return dirname(__DIR__) . '/data/json_data.json';
    }
}
