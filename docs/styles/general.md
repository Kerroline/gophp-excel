Стилизация ячеек осуществляется с помощью объекта `Kerroline\PhpGoExcel\Entities\Style`

Данный объект содержит конфигурацию стиля,
который накладывается на ячейку.

//TODO: add link to all rules
```php
$style = new Style();
$style = Style::make();
$font = Font::make()->setSize(11);
$style->setFont($font);
```

Применить стиль можно к конкретной ячейке, либо к их диапазону
```php
$style = new Style();

$sheet->setCellStyle('A1', $style);
$sheet->setCellRangeStyle('A1', 'B3', $style);
```

А так же аналогичные действия по координатам ячеек:
```php
$style = new Style();

$sheet->setCellStyleByCoordinates(1, 1, $style); // A1
$sheet->setCellStyleByCoordinates(1, 1, 3, 3, $style); // C3
```

! При наложении нескольких стилей на одну ячейку,
все уникальные свойства складываются, все повторяющиеся
свойства перезаписываются последним экземпляром.
```php
$style = new Style();

$fontSize11 = Font::make()->setSize(11);
$wrapText = Alignment::make()->setWrapText();

$style->setFont($fontSize11);
$style->setAlignment($wrapText);

$sheet->setCellStyle('A1', $style);
/** 
 * Final style set A1 cell:
 * Wrapping text
 * Font size 11
 */

$fontHeader = Font::make()
    ->setSize(13) 
    ->setBold()
    ->setFamily('Times New Roman');

$styleForHeader = Style::make()->setFont($fontSizeV2);
$sheet->setCellRangeStyle('A1', 'A3', $styleForHeader);
/** 
 * Final style set A1, A2, A3 cell:
 * Wrapping text
 * !! Font size 13
 * Font Bold
 * Font Family: Times New Roman
 */
```