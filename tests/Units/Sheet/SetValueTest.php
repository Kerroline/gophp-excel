<?php

namespace Sheet;

use PHPUnit\Framework\TestCase;
use Kerroline\PhpGoExcel\Entities\Sheet;

class setValueTest extends TestCase
{
    private const CellAddress = 'A1';
    private const RowIndex = 1;
    private const ColIndex = 1;
    private const CellValue = 123;

    /**
     * @covers Sheet::setCellValueByAddress
     */
    public function testSetCellValueByAddress()
    {
        $sheet = new Sheet('Simple Sheet');

        $reflectionSheet = new \ReflectionClass($sheet);

        $sheet->setCellValueByAddress(
            self::CellAddress,
            self::CellValue
        );

        $cellsProperty = $reflectionSheet->getProperty('filledCellList');
        $cellsProperty->setAccessible(true);

        $cells = $cellsProperty->getValue($sheet);

        $excepted = [
            self::CellAddress => [
                Sheet::CellValueAddressKey => self::CellAddress,
                Sheet::CellValueValueKey => self::CellValue,
            ]
        ];

        $this->assertEquals(
            $cells,
            $excepted
        );
    }

    /**
     * @covers Sheet::setCellValueByCoordinates
     */
    public function testSetCellValueByCoordinate()
    {
        $sheet = new Sheet('Simple Sheet');

        $reflectionSheet = new \ReflectionClass($sheet);

        $sheet->setCellValueByCoordinates(
            self::ColIndex,
            self::RowIndex,
            self::CellValue
        );

        $cellsProperty = $reflectionSheet->getProperty('filledCellList');
        $cellsProperty->setAccessible(true);

        $cells = $cellsProperty->getValue($sheet);

        $excepted = [
            self::CellAddress => [
                Sheet::CellValueAddressKey => self::CellAddress,
                Sheet::CellValueValueKey => self::CellValue,
            ]
        ];

        $this->assertEquals(
            $cells,
            $excepted
        );
    }
}
