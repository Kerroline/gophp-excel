<?php

namespace Kerroline\PhpGoExcel\Entities;

use Kerroline\PhpGoExcel\Interfaces\SerializableEntityInterface;

class Spreadsheet implements SerializableEntityInterface
{
    /**
     * [Description for $sheetList]
     *
     * @var array<Sheet>
     */
    protected $sheetList;

    public function __construct()
    {
        $this->sheetList = [];
    }

    //TODO: Индексация листов
    public function addSheet(Sheet $sheet)
    {
        array_push($this->sheetList, $sheet);

        return $this;
    }

    // public function setActiveSheet()
    // {
    // }

    public function serialize(): array
    {
        $serializedSheets = [];

        /** @var Sheet $sheet */
        foreach ($this->sheetList as $sheet) {
            $serializedSheets[] = $sheet->serialize();
        }

        return [
            'spreadsheet' => [
                'sheetList' => $serializedSheets,
            ]
        ];
    }

    /**
     * @deprecated deprecated since version 0.1.*
     */
    public function save(string $file, string $dataFile, string $commandPath)
    {
        $data = [
            'spreadsheet' => [
                'sheetList' => []
            ]
        ];

        /** @var Sheet $sheet */
        foreach ($this->sheetList as $sheet) {
            $data['spreadsheet']['sheetList'][] = $sheet->serialize();
        }

        file_put_contents($dataFile, json_encode($data));

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //echo 'This is a server using Windows';
            $commandPath .= '.exe';
        } else {
            //echo 'This is a server not using Windows';
        }

        if (!file_exists($commandPath)) {
            throw new \Exception("Php-Go-Excel: config('php-go-excel.go-binary-path') - golang binary file not found");
        }

        $res = exec("{$commandPath} --filename={$file} --dataFilename={$dataFile}", $out, $code);

        if ($code !== 0) {
            throw new \Exception($res, $code);
        }

        unlink($dataFile);
    }
}
