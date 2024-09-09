```
$spreadsheet = new Spreadsheet();

$testSheet = new Sheet('Первый лист');

$testSheet->setCellValue('A1', 123);

$spreadsheet->addSheet($testSheet);

$writer = new DefaultWriter();

$absFilePath = dirname(__DIR__, 2) . '/pge/export_report.xlsx';

$writer->save($spreadsheet, $absFilePath);

```