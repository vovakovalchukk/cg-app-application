define([
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/Element/OrderTable'
], function(
    MapperAbstract,
    OrderTableElement
) {
    var OrderTable = function() {
        MapperAbstract.call(this);

        var optionalAttribs = ['x', 'y'];
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
                ${renderColumns('th', (column, inlineStyles) => {
                    const headerText = column.displayText ? column.displayText : column.optionText;
                    return `<th id="${column.id}-th" style="${inlineStyles}">${headerText}</th>`
                })}
            </tr>
            <tr>
                 ${renderColumns('td', (column, inlineStyles) => (
                    `<td id="${column.id}-td" style="${inlineStyles}">${column.cellPlaceholder}</td>`
                ))}
            </tr>
        </table>`;

        return html;
    };

    OrderTable.prototype.createElement = function() {
        return new OrderTableElement();
    };

    OrderTable.prototype.renderColumns = function(tableColumns, element, tag, render) {
        tableColumns = tableColumns.filter(column => {
            return column;
        });

        return tableColumns.map(column => {
            let inlineStyles = this.getColumnInlineStyles(column, element, tag);
            return render(column, inlineStyles);
        }).join('');
    };

    OrderTable.prototype.getColumnInlineStyles = function(column, element, tag) {
        let inlineStyles = this.getTableStyles(element).slice();
        let activeNodeId = element.getActiveCellNodeId();
        if (activeNodeId === `${column.id}-${tag}`) {
            for(let index = 0; index < inlineStyles.length; index++){
                if(inlineStyles[index].includes('border-color')){
                    inlineStyles[index] = 'border-color: #5fafda';
                    break;
                }
            }
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
});