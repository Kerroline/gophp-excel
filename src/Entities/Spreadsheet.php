<?php

namespace Kerroline\PhpGoExcel\Entities;

use Kerroline\PhpGoExcel\Interfaces\SerializableEntityInterface;

class Spreadsheet implements SerializableEntityInterface
{
    public const ACTIVE_SHEET_KEY = 'activeSheet';
    public const SHEET_LIST_KEY = 'sheetList';


    /** @var array<string,Sheet> */
    protected $sheetList;

    /** @var string */
    protected $activeSheet;

    public function __construct()
    {
        $this->sheetList = [];
    }

    //TODO: Индексация листов
    /**
     * @throws \Exception
     */
    public function addSheet(Sheet $sheet): void
    {
        $sheetTitle = $sheet->getTitle();

        $isSheetExist = array_key_exists(
            $sheetTitle,
            $this->sheetList
        );

        if ($isSheetExist) {
            throw new \Exception('Sheet is already exist');
        }

        $this->sheetList[$sheetTitle] = $sheet;
    }

    /**
     * @param  string|Sheet  $sheetOrTitle
     * @throws \Exception
     */
    public function setActiveSheet($sheetOrTitle)
    {
        if ($sheetOrTitle instanceof Sheet) {
            $sheetOrTitle = $sheetOrTitle->getTitle();
        }

        $isSheetExist = in_array(
            $sheetOrTitle,
            array_keys($this->sheetList)
        );

        if (!$isSheetExist) {
            throw new \Exception('Sheet does not exist');
        }

        $this->activeSheet = $sheetOrTitle;
    }

    public function serialize(): array
    {
        $serializedSheets = [];

        foreach ($this->sheetList as $sheet) {
            $serializedSheets[] = $sheet->serialize();
        }

        return [
            'spreadsheet' => [
                self::SHEET_LIST_KEY => $serializedSheets,
            ]
        ];
    }
}
