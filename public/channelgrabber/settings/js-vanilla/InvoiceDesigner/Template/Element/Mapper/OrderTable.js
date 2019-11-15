define([
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/Element/OrderTable',
    'InvoiceDesigner/Template/Element/Helpers/OrderTable'
], function(
    MapperAbstract,
    TableElement,
    orderTableHelper
) {
    var OrderTable = function() {
        MapperAbstract.call(this);

        const optionalAttribs = ['x', 'y'];
        this.getOptionalAttribs = function() {
            return optionalAttribs;
        };
    };

    OrderTable.prototype = Object.create(MapperAbstract.prototype);

    OrderTable.prototype.getHtmlContents = function(element) {
        const tableColumns = element.getTableColumns().sort((a, b) => {
            return a.position - b.position;
        });
        const tableTotals = element.getTableTotals().sort((a, b) => {
            return a.position - b.position;
        });

        const tableStyles = this.getTableStyles(element, tableColumns);

        const renderColumns = this.renderColumns.bind(this, tableColumns, element);
        const renderTotalRow = this.renderTotalRow.bind(this, tableTotals, element);

        const html = `<table class="template-element-ordertable-main" style="${tableStyles}">
            <tr>
                ${renderColumns('th', (column, inlineStyles, cellId, isActive) => {
                    const headerText = column.displayText ? column.displayText : column.optionText;
                    return `<th>
                        <div id="${cellId}" style="${inlineStyles}" class="u-flex-v-center u-height-100pc u-border-box template-element-cell-container">
                            <div class="template-element-cell-select-container ${isActive ? '-active' : ''}">
                                ${headerText}
                            </div>
                        </div>
                    </th>`
                })}
            </tr>
            <tr>
                 ${renderColumns('td', (column, inlineStyles, cellId, isActive) => (
                    `<td>
                        <div id="${cellId}" style="${inlineStyles}" class="u-flex-v-center u-height-100pc u-border-box template-element-cell-container">
                            <div class="template-element-cell-select-container ${isActive ? '-active' : ''}">
                                ${column.cellPlaceholder}
                            </div>
                         </div>
                    </td>`
                ))}
            </tr>
        </table>
        <div class="template-element-ordertable-totals u-width-100pc">
            ${renderTotalRow(total => (
                `<div class="template-element-totals-row">
                    <span>${total.displayText}</span>
                    <span>${total.placeholder}</span>
                </div>`
            ))}
        </div>`;

        return html;
    };

    OrderTable.prototype.createElement = function() {
        return new TableElement();
    };

    OrderTable.prototype.renderTotalRow = function(tableTotals, element, render) {
        return tableTotals.map(total => {
            return render(total);
        }).join('');
    };

    OrderTable.prototype.renderColumns = function(tableColumns, element, tag, render) {
        return tableColumns.map((column) => {
            let inlineStyles = this.getCellInlineStyles(column, element, tag);
            let cellId = orderTableHelper.generateCellDomId(column.id, tag, element.getId());
            let isActive = isCellTheActiveClickedCell(element, column, tag);
            return render(column, inlineStyles, cellId, isCellTheActiveClickedCell(element, column, tag, isActive));
        }).join('');
    };

    OrderTable.prototype.getCellInlineStyles = function(column, element, tag) {
        const inlineStyles = [];

        if (tag === 'th') {
            inlineStyles.push('border-bottom-style: solid');
            let tableStyles = this.getTableStyles(element, element.getTableColumns()).split('; ');
            let borderWidthStyle = tableStyles.find(style => style.includes('border-width'));
            inlineStyles.push(borderWidthStyle);
        }

        const currentCell =  getCellData(element, column, tag);

        if (!currentCell) {
            return inlineStyles.join('; ');
        }

        applyTextFormattingInlineStyles(inlineStyles, currentCell);
        applyAlignInlineStyle(inlineStyles, currentCell);
        applyFontFamilyInlineStyle(inlineStyles, currentCell);
        applyFontSizeInlineStyle(inlineStyles, currentCell);
        applyFontColourInlineStyle(inlineStyles, currentCell);
        applyBackgroundColourInlineStyle(inlineStyles, currentCell);

        const columnIndexForCell = orderTableHelper.getColumnIndexForCell(element.getTableColumns(), currentCell);
        applyColumnWidth(inlineStyles, element.getTableColumns()[columnIndexForCell]);

        return inlineStyles.join('; ');
    };

    OrderTable.prototype.getTableStyles = function(element, tableColumns) {
        let tableStyles = [];
        const tableAttributes = ['backgroundColour', 'borderWidth', 'borderColour'];

        tableStyles = this.addOptionalDomStyles(
            element,
            tableAttributes,
            tableStyles
        ).flat();

        tableStyles.push(`grid-template-columns: repeat(${tableColumns.length}, 1fr)`);
        tableStyles = tableStyles.join('; ');

        return tableStyles;
    };

    return new OrderTable();

    function applyCellSelectedStyle(inlineStyles) {
        inlineStyles.push('border-color: #5fafda');
        inlineStyles.push('border-style: solid');
    }

    function applyColumnWidth(inlineStyles, column) {
        const width = `width: ${column.width}${column.widthMeasurementUnit}`;
        inlineStyles.push(width);
    }

    function applyBackgroundColourInlineStyle(inlineStyles, currentCell) {
        const backgroundColorStyle = `background-color: ${currentCell.backgroundColour}`;
        inlineStyles.push(backgroundColorStyle);
    }

    function applyFontColourInlineStyle(inlineStyles, currentCell) {
        const fontColourStyle = `color: ${currentCell.fontColour}`;
        inlineStyles.push(fontColourStyle);
    }
    
    function applyFontSizeInlineStyle(inlineStyles, currentCell) {
        const fontSizeStyle = `font-size: ${currentCell.fontSize}pt`;
        inlineStyles.push(fontSizeStyle);
    }

    function applyTextFormattingInlineStyles(inlineStyles, currentCell) {
        currentCell.bold ? inlineStyles.push('font-weight: bold') : inlineStyles.push('font-weight: normal');
        currentCell.italic && inlineStyles.push('font-style: italic');
        currentCell.underline && inlineStyles.push('text-decoration: underline');
    }

    function applyAlignInlineStyle(inlineStyles, currentCell) {
        const alignStyle = getAlignStyle(currentCell);
        inlineStyles.push(alignStyle);
    }

    function applyFontFamilyInlineStyle(inlineStyles, currentCell) {
        if (!currentCell.fontFamily) {
            return;
        }
        const fontStrToApply = currentCell.fontFamily === 'TimesRoman' ? 'Times New Roman' : currentCell.fontFamily;
        inlineStyles.push(`font-family: ${fontStrToApply}`);
    }

    function getAlignStyle(currentCell) {
        if (!currentCell.align) {
            return '';
        }
        return `text-align: ${currentCell.align}`;
    }

    function getCellData(element, column, tag) {
        return element.getTableCells().find(cell => {
            return cell.column === column.id && cell.cellTag === tag;
        });
    }

    function isCellTheActiveClickedCell(element, column, tag) {
        return element.getActiveCellNodeId() === orderTableHelper.generateCellDomId(column.id, tag, element.getId());
    }
});