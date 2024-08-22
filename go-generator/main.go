package main

import (
	"encoding/base64"
	"encoding/json"
	"flag"
	"fmt"
	"os"
	"regexp"
	"strings"
	"unicode/utf8"

	"github.com/xuri/excelize/v2"
)

const SUCCESS_CODE = 0
const UNMARSHAL_DATA_ERROR_CODE = 10
const READ_FILE_ERROR_CODE = 11
const CLOSE_SPREADSHEET_ERROR_CODE = 12
const CREATE_NEW_SHEET_ERROR_CODE = 13
const CREATE_CELL_RANGE_ERROR_CODE = 14
const CREATE_STYLE_ERROR_CODE = 15
const GET_STYLE_ERROR_CODE = 16
const MERGE_STYLE_ERROR_CODE = 17
const SAVE_SPREADSHEET_AS_STRING_ERROR_CODE = 18
const SAVE_SPREADSHEET_AS_FILE_ERROR_CODE = 19
const GET_SHEET_BY_TITLE_ERROR_CODE = 20
const MATCH_COLUMN_ADDRESS_ERROR_CODE = 21

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

type ColumnSettings struct {
	Widths      map[string]float64 `json:"widths,omitempty"`
	AutoSize    map[string]string  `json:"autoSize,omitempty"`
	AllAutoSize bool               `json:"allAutoSize"`
}

type Sheet struct {
	Title          string          `json:"title"`
	CellList       []Cell          `json:"cellList"`
	StyleList      []Style         `json:"styleList"`
	MergeList      []CellRange     `json:"mergeList"`
	ColumnSettings ColumnSettings  `json:"columnSettings"`
	RowsHeight     map[int]float64 `json:"rowHeightList,omitempty"`
}

//endregion Sheet

type Spreadsheet struct {
	SheetList   []Sheet `json:"sheetList"`
	ActiveSheet *string `json:"activeSheet,omitempty"`
}

type Data struct {
	Spreadsheet Spreadsheet `json:"spreadsheet"`
	Filename    string      `json:"filename"`
	AsString    bool        `json:"asString"`
}

func main() {
	data := readData()

	// ---------

	spreadsheet := excelize.NewFile()

	defer func() {
		if err := spreadsheet.Close(); err != nil {
			writeResponse(CLOSE_SPREADSHEET_ERROR_CODE, err.Error())
		}
	}()

	// ---------

	//TODO: Подумать об горутинах

	// Генерируем каждый лист
	for _, sheet := range data.Spreadsheet.SheetList {

		_, err := spreadsheet.NewSheet(sheet.Title)
		if err != nil {
			writeResponse(CREATE_NEW_SHEET_ERROR_CODE, err.Error())
		}

		// Заполняем данными полученные ячейки
		for _, cell := range sheet.CellList {
			spreadsheet.SetCellValue(sheet.Title, cell.Address, cell.Value)

			column := getColumnAddress(cell.Address)
			_, isNeedColumnSetAutoSize := sheet.ColumnSettings.AutoSize[column]

			if isNeedColumnSetAutoSize || sheet.ColumnSettings.AllAutoSize {

				_, isHasCustomWidth := sheet.ColumnSettings.Widths[column]

				if !isHasCustomWidth {
					// Временный экспериментальный костыль
					// Пока не аппрувнут https://github.com/qax-os/excelize/pull/1386
					defaultFontSize := 10
					columnWidth := calculateColumnWidthWithPadding(cell.Value.(string), defaultFontSize)

					sheet.ColumnSettings.Widths[column] = columnWidth
				}
			}
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

		// Применяем все интервалы стилей к текущему листу
		for _, sheetStyle := range sheet.StyleList {

			// Получаем интервал всех ячеек, к которым нужно применить текущий стиль `sheetStyle`
			cellAddressListForStyling, err := rangeCreator(sheetStyle.Range)
			if err != nil {
				writeResponse(CREATE_CELL_RANGE_ERROR_CODE, err.Error())
			}

			createdStyle := createStyle(&sheetStyle)

			createdStyleId, err := spreadsheet.NewStyle(&createdStyle)
			if err != nil {
				writeResponse(CREATE_STYLE_ERROR_CODE, err.Error())
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
							writeResponse(GET_STYLE_ERROR_CODE, err.Error())
						}

						mergedStyle, err := mergeStyles(currentCellStyle, &sheetStyle)
						if err != nil {
							writeResponse(MERGE_STYLE_ERROR_CODE, err.Error())
						}

						mergedStyleId, err := spreadsheet.NewStyle(mergedStyle)
						if err != nil {
							writeResponse(CREATE_STYLE_ERROR_CODE, err.Error())
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

		// место под выставления автоширины колонок

		// Выставляем ширину всех колонок
		for cellSymbol, width := range sheet.ColumnSettings.Widths {
			spreadsheet.SetColWidth(sheet.Title, cellSymbol, cellSymbol, width)
		}

		// Выставляем высоту всех строк
		for rowIndex, height := range sheet.RowsHeight {
			spreadsheet.SetRowHeight(sheet.Title, rowIndex, height)
		}
	}

	// --------

	// Убираем дефолтный лист
	spreadsheet.DeleteSheet("Sheet1")

	if data.Spreadsheet.ActiveSheet != nil {
		idx, err := spreadsheet.GetSheetIndex(*data.Spreadsheet.ActiveSheet)
		if err != nil {
			writeResponse(GET_SHEET_BY_TITLE_ERROR_CODE, err.Error())
		}

		spreadsheet.SetActiveSheet(idx)
	}

	if data.AsString {
		buf, err := spreadsheet.WriteToBuffer()
		if err != nil {
			writeResponse(SAVE_SPREADSHEET_AS_STRING_ERROR_CODE, err.Error())
		}

		excelEncodedString := base64.StdEncoding.EncodeToString(buf.Bytes())

		writeResponse(SUCCESS_CODE, excelEncodedString)
	} else {
		// Сохраняем файл с переданным наименованием
		// Пока не понятно, поддерживает ли оно пути
		if err := spreadsheet.SaveAs(data.Filename); err != nil {
			writeResponse(SAVE_SPREADSHEET_AS_FILE_ERROR_CODE, err.Error())
		}

		writeResponse(SUCCESS_CODE, "Save as file success")
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

	cellAddressList := make([]string, totalCellCount)
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

func readData() Data {
	dataFilenamePtr := flag.String("dataFilename", "", "read file with data to excel")

	flag.Parse()

	jsonData, err := os.ReadFile(*dataFilenamePtr)

	if err != nil {
		writeResponse(READ_FILE_ERROR_CODE, err.Error())
	}

	var data Data

	err = json.Unmarshal(jsonData, &data)
	if err != nil {
		writeResponse(UNMARSHAL_DATA_ERROR_CODE, err.Error())
	}

	return data
}

func writeResponse(code int, content string) {
	fmt.Println(content)
	os.Exit(code)
}

func getColumnAddress(cell string) string {
	regex := regexp.MustCompile(`^[A-Z]+`)

	matches := regex.FindStringSubmatch(cell)

	if len(matches) > 0 {
		return matches[0]
	}

	writeResponse(MATCH_COLUMN_ADDRESS_ERROR_CODE, "No regex match column address by cell address")
	return ""
}

func calculateColumnWidthWithPadding(text string, fontSize int) float64 {
	// Фишка экселя с двусторонним падингом текста
	paddingWidth := calculateColumnWidth("n", fontSize)
	textWidth := calculateColumnWidth(text, fontSize)
	width := paddingWidth + textWidth

	return width
}

const DEFAULT_CHARACTER_WIDTH = 8.26
const APPROXIMATELY_COEFFICIENT = 11.0

func calculateColumnWidth(text string, fontSize int) float64 {
	textWidth := utf8.RuneCountInString(text)
	columnWidth := DEFAULT_CHARACTER_WIDTH * float64(textWidth)

	approxWidth := columnWidth * float64(fontSize) / APPROXIMATELY_COEFFICIENT

	return approxWidth
}
