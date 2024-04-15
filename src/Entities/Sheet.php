<?php

namespace Kerroline\PhpGoExcel\Entities;

use Kerroline\PhpGoExcel\Interfaces\SerializableEntityInterface;

class Sheet implements SerializableEntityInterface
{
    /**
     * [Description for $title]
     *
     * @var string
     */
    protected $title;

    /**
     * [Description for $filledCell]
     *
     * @var array
     */
    protected $filledCellList;

    /**
     * [Description for $styleList]
     *
     * @var array
     */
    protected $styleList;

    /**
     * [Description for $maxRow]
     *
     * @var int
     */
    protected $maxRowIndex;

    /**
     * [Description for $maxColumn]
     *
     * @var int
     */
    protected $maxColumnIndex;

    /**
     * [Description for $mergeCellList]
     *
     * @var array
     */
    protected $mergeCellList;

    /**
     * @var array
     */
    protected $columnWidthList;

    /**
     * @var array
     */
    protected $rowHeightList;


    public function __construct(string $title)
    {
        $this->setTitle($title);

        $this->filledCellList = [];

        $this->styleList = [];

        $this->mergeCellList = [];

        $this->columnWidthList = [];

        $this->rowHeightList = [];
    }

    public function serialize(): array
    {
        $serializedStyleList = [];

        foreach ($this->styleList as $styleSettings) {

            $serializedStyle = array_merge($styleSettings, $styleSettings['style']->serialize());

            unset($serializedStyle['style']);

            $serializedStyleList[] = $serializedStyle;
        }

        return [
            'title'           => $this->title,
            'cellList'        => array_values($this->filledCellList),
            'styleList'       => $serializedStyleList,
            'mergeList'       => array_values($this->mergeCellList),
            'columnWidthList' => $this->columnWidthList,
            'rowHeightList'   => $this->rowHeightList,
        ];
    }


    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }


    #region Test Garbage
    // public function getMaxRowIndex(): int
    // {
    //   return $this->maxRowIndex;
    // }

    // public function getMaxRowNumber(): int
    // {
    //   return $this->getMaxRowIndex();
    // }

    // public function getMaxColumnIndex(): int
    // {
    //   return $this->maxColumnIndex;
    // }

    // public function getMaxColumnSymbol(): int
    // {
    //   return static::stringFromColumnIndex($this->maxColumnIndex);
    // }
    #endregion Test Garbage

    #region Set Cell Value

    //TODO: Validate string and array cell index
    /**
     * [Description for setCellValue]
     *
     * @param string $cell
     * @param mixed $value
     */
    public function setCellValue(string $cell, $value): void
    {
        $this->filledCellList[$cell] = [
            'address' => $cell,
            'value' => $value,
        ];
    }

    /**
     * [Description for setCellValueByCoordinate]
     *
     * @param int $colIndex
     * @param int $rowIndex
     * @param mixed $value
     */
    public function setCellValueByCoordinates(int $colIndex, int $rowIndex, $value): void
    {
        $cell = $this->calculateCellAddress($colIndex, $rowIndex);

        $this->setCellValue($cell, $value);
    }

    public function setRow(int $rowIndex, array $values): void
    {
        // Обрезаем ключи, если вдруг они там заданы
        $values = array_values($values);

        foreach ($values as $colIndex => $value) {
            $colIndex++;

            $this->setCellValueByCoordinates($colIndex, $rowIndex, $value);
        }
    }
    #endregion Set Cell Value

    #region Set Cell Style

    public function setCellStyle(string $cell, Style &$style)
    {
        $this->styleList[] = [
            'range' => [
                'from'  => $cell,
                'to'    => $cell,
            ],
            'style' => $style,
        ];
    }

    public function setCellRangeStyle(string $fromCell, string $toCell, Style &$style)
    {
        $this->styleList[] = [
            'range' => [
                'from'  => $fromCell,
                'to'    => $toCell,
            ],
            'style' => $style,
        ];
    }

    public function setCellStyleByCoordinates(int $colIndex, int $rowIndex, Style &$style)
    {
        $cell = $this->calculateCellAddress($colIndex, $rowIndex);

        $this->styleList[] = [
            'range' => [
                'from'  => $cell,
                'to'    => $cell,
            ],
            'style' => $style,
        ];
    }

    public function setCellStyleByRangeCoordinates(int $fromColIndex, int $fromRowIndex, int $toColIndex, int $toRowIndex, Style &$style)
    {
        $fromCell = $this->calculateCellAddress($fromColIndex, $fromRowIndex);
        $toCell = $this->calculateCellAddress($toColIndex, $toRowIndex);

        $this->styleList[] = [
            'range' => [
                'from'  => $fromCell,
                'to'    => $toCell,
            ],
            'style' => $style,
        ];
    }
    #endregion Set Cell Style

    #region Merge Cell
    public function mergeCellByAddress(string $fromCell, string $toCell)
    {
        $this->mergeCellList[] = [
            'from'  => $fromCell,
            'to'    => $toCell,
        ];
    }

    public function mergeCellByCoordinate(int $fromColIndex, int $fromRowIndex, int $toColIndex, int $toRowIndex)
    {
        $fromCell = $this->calculateCellAddress($fromColIndex, $fromRowIndex);
        $toCell = $this->calculateCellAddress($toColIndex, $toRowIndex);

        $this->mergeCellList[] = [
            'from'  => $fromCell,
            'to'    => $toCell,
        ];
    }

    // public function mergeRow(string $fromCell, int $count)
    // {
    // }

    // public function mergeCol(string $fromCell, int $count)
    // {
    // }
    #endregion Merge Cell

    #region Column and Row Size
    //TODO: Validate Sheet set columns width methods
    /**
     * [Description for setColumnWidthByAddress]
     *
     * @param string $colSymbol (A)
     * @param int $width
     *
     * @return [type]
     *
     */
    public function setColumnWidthByAddress(string $colSymbol, int $width)
    {
        $this->columnWidthList[$colSymbol] = $width;

        return $this;
    }

    /**
     * [Description for setColumnWidthByIndex]
     *
     * @param int $colIndex (A = 1)
     * @param int $width
     *
     * @return [type]
     *
     */
    public function setColumnWidthByIndex(int $colIndex, int $width)
    {
        $colSymbol = static::stringFromColumnIndex($colIndex);

        $this->columnWidthList[$colSymbol] = $width;

        return $this;
    }

    /**
     * [Description for setColumnsWidth]
     *
     * [
     *  'A' => 10,
     *  'B' => 12,
     *  ...
     * ]
     *
     * @param array $columns
     *
     * @return [type]
     *
     */
    public function setColumnsWidth(array $columns)
    {
        foreach ($columns as $colSymbol => $width) {
            $this->columnWidthList[$colSymbol] = $width;
        }

        return $this;
    }

    public function setRowHeight(int $rowIndex, int $height)
    {
        $this->rowHeightList[$rowIndex] = $height;
    }

    public function setRowsHeight(array $rows)
    {
        foreach ($rows as $rowIndex => $height) {
            $this->rowHeightList[$rowIndex] = $height;
        }

        return $this;
    }
    #endregion Column and Row Size

    #region PHP Spreadsheet Coordinate methods

    /**
     * A1 = 1, 1
     *
     * @param int $colIndex
     * @param int $rowIndex
     *
     * @return [type]
     *
     */
    protected function calculateCellAddress(int $colIndex, int $rowIndex): string
    {
        $symbol = static::stringFromColumnIndex($colIndex);

        return  "{$symbol}{$rowIndex}";
    }

    /**
     * Column index from string.
     *
     * @param string $pString eg 'A'
     *
     * @return int Column index (A = 1)
     */
    public static function columnIndexFromString(string $pString): int
    {
        //    Using a lookup cache adds a slight memory overhead, but boosts speed
        //    caching using a static within the method is faster than a class static,
        //        though it's additional memory overhead
        static $indexCache = [];

        if (isset($indexCache[$pString])) {
            return $indexCache[$pString];
        }
        //    It's surprising how costly the strtoupper() and ord() calls actually are, so we use a lookup array rather than use ord()
        //        and make it case insensitive to get rid of the strtoupper() as well. Because it's a static, there's no significant
        //        memory overhead either
        static $columnLookup = [
            'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9, 'J' => 10, 'K' => 11, 'L' => 12, 'M' => 13,
            'N' => 14, 'O' => 15, 'P' => 16, 'Q' => 17, 'R' => 18, 'S' => 19, 'T' => 20, 'U' => 21, 'V' => 22, 'W' => 23, 'X' => 24, 'Y' => 25, 'Z' => 26,
            'a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6, 'g' => 7, 'h' => 8, 'i' => 9, 'j' => 10, 'k' => 11, 'l' => 12, 'm' => 13,
            'n' => 14, 'o' => 15, 'p' => 16, 'q' => 17, 'r' => 18, 's' => 19, 't' => 20, 'u' => 21, 'v' => 22, 'w' => 23, 'x' => 24, 'y' => 25, 'z' => 26,
        ];

        //    We also use the language construct isset() rather than the more costly strlen() function to match the length of $pString
        //        for improved performance
        if (isset($pString[0])) {
            if (!isset($pString[1])) {
                $indexCache[$pString] = $columnLookup[$pString];

                return $indexCache[$pString];
            } elseif (!isset($pString[2])) {
                $indexCache[$pString] = $columnLookup[$pString[0]] * 26 + $columnLookup[$pString[1]];

                return $indexCache[$pString];
            } elseif (!isset($pString[3])) {
                $indexCache[$pString] = $columnLookup[$pString[0]] * 676 + $columnLookup[$pString[1]] * 26 + $columnLookup[$pString[2]];

                return $indexCache[$pString];
            }
        }

        throw new \Exception('Column string index can not be ' . ((isset($pString[0])) ? 'longer than 3 characters' : 'empty'));
    }

    /**
     * String from column index.
     *
     * @param int $columnIndex Column index (A = 1)
     *
     * @return string
     */
    public static function stringFromColumnIndex(int $columnIndex): string
    {
        static $indexCache = [];

        if (!isset($indexCache[$columnIndex])) {
            $indexValue = $columnIndex;
            $base26 = null;
            do {
                $characterValue = ($indexValue % 26) ?: 26;
                $indexValue = ($indexValue - $characterValue) / 26;
                $base26 = chr($characterValue + 64) . ($base26 ?: '');
            } while ($indexValue > 0);
            $indexCache[$columnIndex] = $base26;
        }

        return $indexCache[$columnIndex];
    }
    #endregion PHP Spreadsheet Coordinate methods
}
