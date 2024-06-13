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

    /**
     * @throws \Exception
     */
    public function addSheet(Sheet $sheet): void
    {
        $sheetTitle = $sheet->getTitle();

        $isSheetExist = $this->isSheetExist($sheetTitle);

        if ($isSheetExist) {
            throw new \Exception('Sheet is already exist');
        }

        $this->sheetList[$sheetTitle] = $sheet;
    }

    /**
     * @param  string|Sheet  $sheetOrTitle
     * @throws \Exception
     */
    public function setActiveSheet($sheetOrTitle): void
    {
        if ($sheetOrTitle instanceof Sheet) {
            $sheetOrTitle = $sheetOrTitle->getTitle();
        }

        $isSheetExist = $this->isSheetExist($sheetOrTitle);

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
            self::SHEET_LIST_KEY => $serializedSheets,
        ];
    }


    private function isSheetExist(string $title): bool
    {
        return array_key_exists(
            $title,
            $this->sheetList
        );
    }
}
