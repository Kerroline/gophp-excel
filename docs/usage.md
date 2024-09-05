ref `Kerroline/PhpGoExcel/Entities/Spreadsheet`
ref `Kerroline/PhpGoExcel/Entities/Sheet`
ref `Kerroline/PhpGoExcel/DefaultWriter`
ref `Kerroline/PhpGoExcel/Entities/Internals/BaseWriter`

# Создание Excel объекта-коллекции
Корневной объект-коллекция, который содержит в себе все листы и
дополнительные настройки, которые используются для создания Excel-файла.
```php
$spreadsheet = new Spreadsheet();
```
Создание типового листа с данными
```php
$simpleSheet = new Sheet('Title');
```

При наличии нескольких листов можно указать главный, который будет 
отображаться при открытии эксель файла 
```php
$simpleSheet = new Sheet('Title');
$anotherSheet = new Sheet('Another');

$spreadsheet->addSheet($simpleSheet);
$spreadsheet->addSheet($anotherSheet);

$spreadsheet->setActiveSheet($anotherSheet);
$spreadsheet->setActiveSheet('Title');
```

! Перед установкой активного листа, необходимо добавить его в 
объект-коллекцию. При отсутствии указанного наименования/объекта 
листа в коллекции, будет выброшено исключение. 

# Генерация эксель файла

Генерация Excel-файла происходит с помощью бинарного go файла,
который собран для определенного перечня OS и защит в пакет.


Генерация файла реализовано двумя способами:
* Стороковым представлением xlm разметки
* Excel файлом на диск

```php
$writer = new DefaultWriter();

$result = $writer->saveAsString($spreadsheet);
$writer->saveAsFile($spreadsheet, '/var/www/data/my_excel_file.xlsx');
```

При необходимости вы можете сгенерировать бинарный файл самостоятельно
//TODO: См. детально го-репу
И переопределить его местоположение путем наследования
базового класса генерации `BaseWriter` с заменой результата
метода `getGeneratorCommandPath()`, см. прим. `DefaultWriter`

