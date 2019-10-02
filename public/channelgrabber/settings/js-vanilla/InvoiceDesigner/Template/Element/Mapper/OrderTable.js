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
        const tableInlineStyles = this.getTableStyles(element).slice();
        const renderColumns = this.renderColumns.bind(this, tableColumns, element);

        const html = `<table class="template-element-ordertable-main" style="${tableInlineStyles}">
            <tr>
                ${renderColumns('th', (column, inlineStyles, cellId) => {
                    const headerText = column.displayText ? column.displayText : column.optionText;
                    return `<th id="${cellId}" style="${inlineStyles}">${headerText}</th>`
                })}
            </tr>
            <tr>
                 ${renderColumns('td', (column, inlineStyles, cellId) => (
                    `<td id="${cellId}" style="${inlineStyles}">${column.cellPlaceholder}</td>`
                ))}
            </tr>
        </table>`;

        return html;
    };

    OrderTable.prototype.createElement = function() {
        return new TableElement();
    };

    OrderTable.prototype.renderColumns = function(tableColumns, element, tag, render) {
        return tableColumns.map(column => {
            let inlineStyles = this.getCellInlineStyles(column, element, tag);
            let cellId = orderTableHelper.generateCellDomId(column.id, tag, element.getId());
            return render(column, inlineStyles, cellId);
        }).join('');
    };

    OrderTable.prototype.getCellInlineStyles = function(column, element, tag) {
        const inlineStyles = this.getTableStyles(element).slice();
        const activeNodeId = element.getActiveCellNodeId();
        const cellNodeIdForCell = orderTableHelper.generateCellDomId(column.id, tag, element.getId());
        const currentCell =  element.getTableCells().find(cell => {
            return cell.column === column.id && cell.cellTag === tag;
        });

        if (activeNodeId === cellNodeIdForCell) {
            applyCellSelectedStyle(inlineStyles);
        }

        if (!currentCell) {
            return inlineStyles.join('; ');
        }

        applyTextFormattingInlineStyles(inlineStyles, currentCell);
        applyAlignInlineStyle(inlineStyles, currentCell);
        applyFontFamilyInlineStyle(inlineStyles, currentCell);
        applyFontSizeInlineStyle(inlineStyles, currentCell);
        applyFontColourInlineStyle(inlineStyles, currentCell);
        applyBackgroundColourInlineStyle(inlineStyles, currentCell);

        if (tag === 'th') {
            const tableColumns = element.getTableColumns();
            const columnIndexForCell = orderTableHelper.getColumnIndexForCell(tableColumns, currentCell);
            applyColumnWidth(inlineStyles, tableColumns[columnIndexForCell]);
        }

        return inlineStyles.join('; ');
    };

    OrderTable.prototype.getTableStyles = function(element) {
        let tableStyles = [];
        const tableAttributes = ['backgroundColour', 'borderWidth', 'borderColour'];
        tableStyles = this.addOptionalDomStyles(element, tableAttributes, tableStyles);
        if (element.getBorderWidth()) {
            tableStyles.push('border-style: solid');
        }
        return tableStyles;
    };

    return new OrderTable();

    function applyCellSelectedStyle(inlineStyles) {
        for (let index = 0; index < inlineStyles.length; index++) {
            if (!inlineStyles[index].includes('border-color')) {
                continue;
            }
            inlineStyles[index] = 'border-color: #5fafda';
            break;
        }
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
});