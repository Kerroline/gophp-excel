<?php

namespace Spreadsheet;

use Exception;
use Kerroline\PhpGoExcel\Entities\Sheet;
use PHPUnit\Framework\TestCase;
use Kerroline\PhpGoExcel\Entities\Spreadsheet;

class setActiveSheetTest extends TestCase
{
    private const SHEET_TITLE = 'Sh-tl';

    /**
     * @covers Spreadsheet::setActiveSheet
     */
    public function testIsMethodExist()
    {
        $spreadsheet = new Spreadsheet();

        $this->assertTrue(
            method_exists($spreadsheet, 'setActiveSheet'),
            'Spreadsheet does not have method setActiveSheet'
        );
    }

    /**
     * @covers Spreadsheet::setActiveSheet
     */
    public function testSetActiveSheetBySheet()
    {
        $spreadsheet = new Spreadsheet();

        $reflectionSpreadsheet = new \ReflectionClass($spreadsheet);

        $sheet = new Sheet(self::SHEET_TITLE);

        $spreadsheet->addSheet($sheet);

        $spreadsheet->setActiveSheet($sheet);

        $asProperty = $reflectionSpreadsheet->getProperty(Spreadsheet::ACTIVE_SHEET_KEY);
        $asProperty->setAccessible(true);

        $activeSheet = $asProperty->getValue($spreadsheet);

        $excepted = self::SHEET_TITLE;

        $this->assertEquals(
            $excepted,
            $activeSheet
        );
    }

    /**
     * @covers Spreadsheet::setActiveSheet
     */
    public function testSetActiveSheetByTitle()
    {
        $spreadsheet = new Spreadsheet();

        $reflectionSpreadsheet = new \ReflectionClass($spreadsheet);

        $sheet = new Sheet(self::SHEET_TITLE);

        $spreadsheet->addSheet($sheet);

        $spreadsheet->setActiveSheet(self::SHEET_TITLE);

        $asProperty = $reflectionSpreadsheet->getProperty(Spreadsheet::ACTIVE_SHEET_KEY);
        $asProperty->setAccessible(true);

        $activeSheet = $asProperty->getValue($spreadsheet);

        $excepted = self::SHEET_TITLE;

        $this->assertEquals(
            $excepted,
            $activeSheet
        );
    }

    /**
     * @covers Spreadsheet::setActiveSheet
     */
    public function testSetActiveSheetByInvalidTitle()
    {
        $spreadsheet = new Spreadsheet();

        $sheet = new Sheet(self::SHEET_TITLE);

        $spreadsheet->addSheet($sheet);

        $this->expectException(Exception::class);

        $spreadsheet->setActiveSheet('Invalid-Title');
    }
}
