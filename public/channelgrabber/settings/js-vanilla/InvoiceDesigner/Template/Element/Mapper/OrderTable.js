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
        const inlineStyles = this.getTableStyles(element);

        const renderColumns = this.renderColumns.bind(this, tableColumns);

        const html = `<table class="template-element-ordertable-main" style="${inlineStyles}">
            <tr>
                ${renderColumns(column => {
                    const headerText = column.displayText ? column.displayText : column.optionText;
                    return `<th style="${inlineStyles}">${headerText}</th>`
                })}
            </tr>
            <tr>
                 ${renderColumns(column => (
                    `<td style="${inlineStyles}">${column.cellPlaceholder}</td>`
                ))}
            </tr>
        </table>`;

        return html;
    };

    OrderTable.prototype.createElement = function() {
        console.log('in create element');
        return new OrderTableElement();
    };

    OrderTable.prototype.renderColumns = function(tableColumns, render) {
        return tableColumns.map(column => (
            render(column)
        )).join('');
    };

    OrderTable.prototype.getTableStyles = function(element) {
        let tableStyles = [];
        const tableAttributes = ['backgroundColour', 'borderWidth', 'borderColour'];
        tableStyles = this.addOptionalDomStyles(element, tableAttributes, tableStyles);
        if (element.getBorderWidth()) {
            tableStyles.push('border-style: solid');
        }
        return tableStyles.join('; ');
    };

    return new OrderTable();
});