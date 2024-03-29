package main

import (
	"encoding/json"
	"flag"
	"fmt"
	"log"
	"os"
	"strings"

	"github.com/xuri/excelize/v2"
)

type CellRange struct {
	From string `json:"from"`
	To   string `json:"to"`
}

// region Style
type Font struct {
	Bold   *bool    `json:"bold"`
	Italic *bool    `json:"italic"`
	Family *string  `json:"family"`
	Size   *float64 `json:"size"`
}

type Alignment struct {
	WrapText   *bool   `json:"wrapText"`
	Horizontal *string `json:"horizontal"`
	Vertical   *string `json:"vertical"`
}

type Border struct {
	Type  *string `json:"type"`
	Color *string `json:"color"`
	Style *int    `json:"style"`
}

type Style struct {
	Borders      *[]Border  `json:"borders"`
	Font         *Font      `json:"font"`
	Alignment    *Alignment `json:"alignment"`
	NumFmt       *int       `json:"numberFormat"`
	CustomNumFmt *string    `json:"customNumberFormat"`
	Range        CellRange  `json:"range"`
}

//endregion Style

// region Sheet
type Cell struct {
	Address string      `json:"address"`
	Value   interface{} `json:"value"`
}

type Sheet struct {
	Title        string         `json:"title"`
	CellList     []Cell         `json:"cellList"`
	StyleList    []Style        `json:"styleList"`
	MergeList    []CellRange    `json:"mergeList"`
	ColumnsWidth map[string]int `json:"columnWidthList"`
	RowsHeight   map[int]int    `json:"rowHeightList"`
}

//endregion Sheet

type Spreadsheet struct {
	SheetList []Sheet `json:"sheetList"`
}

type Data struct {
	Spreadsheet Spreadsheet `json:"spreadsheet"`
}

func main() {
	filenamePtr := flag.String("filename", "default_filename", "generated excel filename")

	dataFilenamePtr := flag.String("dataFilename", "", "read file with data to excel")

	// --------

	flag.Parse()

	// --------

	jsonData, err := os.ReadFile(*dataFilenamePtr)

	if err != nil {
		fatal("os.ReadFile(*dataFilenamePtr)", err)
		log.Fatal(err)
	}

	// ---------

	spreadsheet := excelize.NewFile()

	defer func() {
		if err := spreadsheet.Close(); err != nil {
			fatal("spreadsheet.Close()", err)
			log.Fatal(err)
		}
	}()

	// --------

	var data Data

	json.Unmarshal(jsonData, &data)

	// ---------

	// Генерируем каждый лист
	for _, sheet := range data.Spreadsheet.SheetList {

		_, err := spreadsheet.NewSheet(sheet.Title)
		if err != nil {
			fatal("spreadsheet.NewSheet(sheet.Title)", err)
			log.Fatal(err)
			return
		}

		// Заполняем данными полученные ячейки
		for _, cell := range sheet.CellList {
			spreadsheet.SetCellValue(sheet.Title, cell.Address, cell.Value)
		}

		// Выставляем ширину всех колонок
		for cellSymbol, width := range sheet.ColumnsWidth {
			spreadsheet.SetColWidth(sheet.Title, cellSymbol, cellSymbol, float64(width))
		}

		// Выставляем высоту всех строк
		for rowIndex, height := range sheet.RowsHeight {
			spreadsheet.SetRowHeight(sheet.Title, rowIndex, float64(height))
		}

		/*
			Словарь Адрес ячейки - айди её стиля
			Необходим при генерации стилей для ячеек

			Проблема: при записи стиля в ячейку он перезаписывает предыдущий стиль
			необходимо для каждой ячейки иметь единый объект стиля, который будет
			модифицироваться в процессе
		*/

		cellAddressStyleIdSequenceDictionary := make(map[string][]int)

		// Временная болванка, которая содержит ключ объединенных стилей и айди получившегося нового стиля
		mapper := make(map[string]int)

		//TODO: Подумать об горутинах

		// Применяем все интервалы стилей к текущему листу
		for _, sheetStyle := range sheet.StyleList {

			// Получаем интервал всех ячеек, к которым нужно применить текущий стиль `sheetStyle`
			cellAddressListForStyling, err := rangeCreator(sheetStyle.Range)
			if err != nil {
				fatal("rangeCreator(sheetStyle.Range)", err)
				log.Fatal(err)
				return
			}

			createdStyle := createStyle(&sheetStyle)

			createdStyleId, err := spreadsheet.NewStyle(&createdStyle)
			if err != nil {
				fatal("spreadsheet.NewStyle(&createdStyle)", err)
				log.Fatal(err)
				return
			}

			// Для каждой ячейки записываем стиль в `cellAddressStyleIdSequenceDictionary`
			for _, cellAddress := range cellAddressListForStyling {
				styleIdSequence, isCellExistStyle := cellAddressStyleIdSequenceDictionary[cellAddress]

				cellStyleId := createdStyleId

				/*
					Если для текущей ячейки `cellAddress` ранее уже была создана последовательность стилей
					в `cellAddressStyleIdSequenceDictionary`,
					то:
						Генерируем уникальный ключ комбинации стилей и проверяем, был ли создан такой
						комбинированный стиль ранее

						Если такой ключ уже есть в `mapper`, то:
							Достаем из маппера айди стиля, записываем его к текущей ячейке
						Иначе:
							Создаем новый стиль из ранее записанного и только что созданного.
							Помещаем этот стиль в маппер по уникальному ключу.
							Записываем в ячейку айди созданного стиля
					Иначе:
						Добавляем для текущей ячейки в словарь айди этого стиля

				*/

				// Если у ячейки уже есть стили
				if isCellExistStyle {
					sequence := styleIdSequence
					sequence = append(sequence, createdStyleId)
					// Уникальный ключи комбинации стилей
					mapperKey := strings.Trim(strings.Join(strings.Fields(fmt.Sprint(sequence)), "."), "[]")

					styleInMapperId, isMergedStyleCreated := mapper[mapperKey]
					// Если уже создавался новый стиль по этой комбинации, просто берем айди того стиля
					if isMergedStyleCreated {
						cellStyleId = styleInMapperId
					} else {
						currentCellStyleId := styleIdSequence[len(styleIdSequence)-1]

						currentCellStyle, err := spreadsheet.GetStyle(currentCellStyleId)
						if err != nil {
							fatal("spreadsheet.GetStyle(currentCellStyleId)", err)
							log.Fatal(err)
							return
						}

						mergedStyle, err := mergeStyles(currentCellStyle, &sheetStyle)
						if err != nil {
							fatal("mergeStyles(currentCellStyle, &sheetStyle)", err)
							log.Fatal(err)
							return
						}

						mergedStyleId, err := spreadsheet.NewStyle(mergedStyle)
						if err != nil {
							fatal("spreadsheet.NewStyle(mergedStyle)", err)
							log.Fatal(err)
							return
						}

						mapper[mapperKey] = mergedStyleId

						cellStyleId = mergedStyleId
					}

				} else {
					// Вроде бы ничего не делаем в `cellStyleId` и так лежит айди стиля для этой ячейки
				}

				cellAddressStyleIdSequenceDictionary[cellAddress] = append(cellAddressStyleIdSequenceDictionary[cellAddress], cellStyleId)
			}
		}

		/*
			Сгруппировать словарь всех ячеек по последнему айди последовательности
			Так мы получим все ячейки, которые имеют один стиль
			Из этих ячеек можно будет построить интервал
			Применить стиль с этим айди к этому интервалу
		*/
		for cellAddress, StyleIdSequence := range cellAddressStyleIdSequenceDictionary {
			styleId := StyleIdSequence[len(StyleIdSequence)-1]

			spreadsheet.SetCellStyle(sheet.Title, cellAddress, cellAddress, styleId)
		}

		// Мерджим все ячейки для этого листа
		for _, cellRange := range sheet.MergeList {
			spreadsheet.MergeCell(sheet.Title, cellRange.From, cellRange.To)
		}

	}

	// --------

	// Убираем дефолтный лист
	spreadsheet.DeleteSheet("Sheet1")

	// Сохраняем файл с переданным наименованием
	// Пока не понятно, поддерживает ли оно пути
	if err := spreadsheet.SaveAs(*filenamePtr); err != nil {
		fatal("spreadsheet.SaveAs(*filenamePtr)", err)
		log.Fatal(err)
	}
}

/*
Генерирует последовательность всех ячеек для переданного интервала
Прим:
CellRange = { From: A1, To : B3 }
Return: [ A1, A2, A3, B1, B2, B3 ]
*/
func rangeCreator(cellRange CellRange) ([]string, error) {
	fromColIndex, fromRowIndex, err := excelize.CellNameToCoordinates(cellRange.From)
	if err != nil {
		return nil, err
	}

	toColIndex, toRowIndex, err := excelize.CellNameToCoordinates(cellRange.To)
	if err != nil {
		return nil, err
	}

	totalCellCount := (toColIndex - fromColIndex + 1) * (toRowIndex - fromRowIndex + 1)

	cellAddressList := make([]string, totalCellCount, totalCellCount)
	iterator := 0

	for i := fromColIndex; i <= toColIndex; i++ {
		for j := fromRowIndex; j <= toRowIndex; j++ {
			cellName, err := excelize.CoordinatesToCellName(i, j)
			if err != nil {
				return nil, err
			}

			cellAddressList[iterator] = cellName
			iterator++
		}
	}

	return cellAddressList, nil
}

func countIntersection(styleList []Style) int {

	return 0
}

/*
Создание Эксель стиля, у которого будут выставлены
переданные ключи-значения
*/
func createStyle(sourceStyle *Style) excelize.Style {
	style := excelizeStyleInit()

	fillStyle(&style, sourceStyle)

	return style
}

/*
Создание Эксель стиля со всеми полями по-умолчанию
*/
func excelizeStyleInit() excelize.Style {
	defaultDecimalPlaces := 0
	defaultDecimalPlacesPointer := &defaultDecimalPlaces

	// defaultCustomNumberFormat := "General"
	// defaultCustomNumberFormaPointer := &defaultCustomNumberFormat

	return excelize.Style{
		Alignment:     &excelize.Alignment{},
		Font:          &excelize.Font{},
		Protection:    &excelize.Protection{},
		DecimalPlaces: defaultDecimalPlacesPointer,
		// CustomNumFmt:  defaultCustomNumberFormaPointer,
	}
}

/*
Модифицирует полученный по ссылке Эксель стиль
Выставляет ему переданные ключи-значения
*/
func fillStyle(targetStyle *excelize.Style, sourceStyle *Style) {
	if sourceStyle.Borders != nil {
		for _, border := range *sourceStyle.Borders {
			excelizeBorder := &excelize.Border{}

			if border.Type != nil {
				excelizeBorder.Type = *border.Type
			}

			if border.Color != nil {
				excelizeBorder.Color = *border.Color
			}

			if border.Style != nil {
				excelizeBorder.Style = *border.Style
			}

			targetStyle.Border = append(targetStyle.Border, *excelizeBorder)
		}
	}

	if sourceStyle.Font != nil {
		if sourceStyle.Font.Bold != nil {
			targetStyle.Font.Bold = *sourceStyle.Font.Bold
		}

		if sourceStyle.Font.Italic != nil {
			targetStyle.Font.Italic = *sourceStyle.Font.Italic
		}

		if sourceStyle.Font.Family != nil {
			targetStyle.Font.Family = *sourceStyle.Font.Family
		}

		if sourceStyle.Font.Size != nil {
			targetStyle.Font.Size = *sourceStyle.Font.Size
		}
	}

	if sourceStyle.Alignment != nil {
		if sourceStyle.Alignment.WrapText != nil {
			targetStyle.Alignment.WrapText = *sourceStyle.Alignment.WrapText
		}

		if sourceStyle.Alignment.Horizontal != nil {
			targetStyle.Alignment.Horizontal = *sourceStyle.Alignment.Horizontal
		}

		if sourceStyle.Alignment.Vertical != nil {
			targetStyle.Alignment.Vertical = *sourceStyle.Alignment.Vertical
		}
	}

	if sourceStyle.NumFmt != nil {
		targetStyle.NumFmt = *sourceStyle.NumFmt
	}

	if sourceStyle.CustomNumFmt != nil {
		targetStyle.CustomNumFmt = sourceStyle.CustomNumFmt
	}
}

/*
Возвращает новый Эксель стиль, который будет основан на переданном
с измененным в нем ключами-значениями
*/
func mergeStyles(existStyle *excelize.Style, sourceStyle *Style) (*excelize.Style, error) {
	style, err := deepCopyStyle(existStyle)
	if err != nil {
		return nil, err
	}

	fillStyle(style, sourceStyle)

	return style, nil
}

/*
Создает копию Эксель стиля
*/
func deepCopyStyle(existStyle *excelize.Style) (*excelize.Style, error) {
	existStyleJSON, err := json.Marshal(existStyle)
	if err != nil {
		return nil, err
	}

	clone := excelizeStyleInit()
	if err = json.Unmarshal(existStyleJSON, &clone); err != nil {
		return nil, err
	}

	return &clone, nil
}

func fatal(message string, err error) {
	fmt.Println(message)
	fmt.Printf("%+v\n", err)
	log.Fatal(err)
}
