apt update

apt install wget

wget https://dl.google.com/go/go1.21.5.linux-amd64.tar.gz

sudo tar -C /opt -xzf go1.21.5.linux-amd64.tar.gz

export PATH=$PATH:/opt/go/bin

go version 

MUST BE EQUAL `go version go1.21.5 linux/amd64`

cd vendor/kerroline/gophp-excel/go-generator/

go build -o bin/generator main.go

- cd root

cd app

mkdir PhpGoExcel

- cd root

mv vendor/kerroline/gophp-excel/go-generator/bin/generator app/PhpGoExcel/generator

Examples until 0.1.*: 

```
$report = new Spreadsheet();

$testSheet = new Sheet('Первый лист');

$testSheet->setCellValue('A1', 123);

$report->addSheet($testSheet);

$fileName2 = 'Тестовый отчет на ГО.xlsx';
$filePath = dirname(__DIR__, 2) . '/pge/export_report.xlsx';
$dataFile = dirname(__DIR__, 2) . '/pge/data_file.json';
$commandPath = dirname(__DIR__, 2) . '/pge/generator';

$report->save(
    $filePath,
    $dataFile,
    $commandPath,
);
```
