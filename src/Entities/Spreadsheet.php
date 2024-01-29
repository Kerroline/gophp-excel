<?php

namespace Kerroline\PhpGoExcel\Entities;

class Spreadsheet
{
    /**
     * [Description for $sheetList]
     *
     * @var array
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

    // public function addGlobalStyle(Style &$style)
    // {
    // }


    public function save(string $filename)
    {
        $dataFilename = 'report_data.json';

        $data = [
            'spreadsheet' => [
                'sheetList' => []
            ]
        ];

        foreach ($this->sheetList as $sheet) {
            $data['spreadsheet']['sheetList'][] = $sheet->serialize();
        }

        file_put_contents($dataFilename, json_encode($data));

        $commandPath = config('php-go-excel.go-binary-path');

        $res = exec("{$commandPath} --filename={$filename} --dataFilename={$dataFilename}", $out, $code);

        if ($code !== 0) {
            throw new \Exception($res, $code);
        }
    }
}
