Excel generator from JSON

Реализация для `PHP`: link keeroline/gophp-excel

Сборка бинарного файла:
```linux
apt update

apt install wget

wget https://dl.google.com/go/go1.21.5.linux-amd64.tar.gz

tar -C /opt -xzf go1.21.5.linux-amd64.tar.gz

export PATH=$PATH:/opt/go/bin

go version 

MUST BE EQUAL `go version go1.21.5 linux/amd64`

go build -o generator main.go
```
Usage
```
./generator --dataFilename=/var/www/data/my_excel_data.json
```

Поддерживаемый формат JSON структуры:
```json
{
    "spreadsheet": {
        "sheetList": [
            {
                "title": string,
                "cellList": [
                    { 
                        "address": string, 
                        "value": any 
                    }
                ],
                "styleList": [
                    {
                        "borders": [
                            {
                                "type": string|null,
                                "color": string|null,
                                "style": int|null,
                            },
                        ]|null,
                        "font": {
                            "bold": bool|null,
                            "italic": bool|null,
                            "family": string|null,
                            "size": float|null,
                        }|null,
                        "alignment": {
                            "wrapText": bool|null,
                            "horizontal": string|null,
                            "vertical": string|null,
                        }|null,
                        "numberFormat": integer|null,
                        "customNumberFormat": string|null,
                        "range": { 
                            "from": string, 
                            "to": string 
                        },
                    }
                ],
                "mergeList": [
                    { "from": string, "to": string },
                ],
                "columnSettings": {
                    "widths": {
                        // key: value
                        string: float,
                    },
                    "autoSize": {
                        // key: value
                        string: string,
                    },
                    "allAutoSize": boolean,
                },
                "rowHeightList": {
                    // key: value
                    int: float,
                }|null,
            },
        ],
        "activeSheet": string|null,
    },
    "filename": string,
    "asString": boolean,
}
```

Спецификация каждого ключа:
```
spreadsheet - Корневой объект, который содержит все допустимые настройки для будущего Excel файла.
filename - Конечный путь до файла, который будет сгенерирован. Прим: '/var/www/data/reports/my_excel_report.xlsx'
asString - Должен ли генератор вернуть строковое представление Exce файла, вместо его создания на диске. При выставленом флаге
true, параметр filename игнорируется. Обратите внимание, что команда вернет строкове представление в формате base64.

sheetList - массив листов, который будет создан в файле. Содержит данные для ячеек и настройки их стилизаций.
activeSheet - наименование листа, который станет активным при открытии Excel файла

title - наименование листа, должно быть уникальным в рамках одного Excel файла

cellList - массив ячеек с данными, которые будут заданы в лист, в котором находятся

```



