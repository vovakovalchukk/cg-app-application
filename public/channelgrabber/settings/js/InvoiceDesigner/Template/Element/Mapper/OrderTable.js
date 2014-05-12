define([
    'InvoiceDesigner/Template/Element/MapperAbstract'
], function(
    MapperAbstract
) {
    var OrderTable = function()
    {
        MapperAbstract.call(this);

        var optionalAttribs = ['x', 'y'];
        this.getOptionalAttribs = function()
        {
            return optionalAttribs;
        };
    };

    OrderTable.prototype = Object.create(MapperAbstract.prototype);

    OrderTable.prototype.getHtmlContents = function(element)
    {
        var tableStyles = [];
        var tableAttributes = ['backgroundColour', 'borderWidth', 'borderColour'];
        tableStyles = this.addOptionalDomStyles(element, tableAttributes, tableStyles);
        if (element.getBorderWidth()) {
            tableStyles.push('border-style: solid');
        }
        var cssStyle = tableStyles.join('; ');

        var tableHeaders = ['Qty', 'Product #', 'Description', 'Price', 'Total'];
        var tableData = ['2', '7788934-2', 'Duracell Battery 10pc', '&pound;2.00', '&pound;4.00'];

        var table = '<table style="'+cssStyle+'">\n';
        table += '<thead>\n';
        for (var key in tableHeaders) {
            table += '<th style="'+cssStyle+'">'+tableHeaders[key]+'</th>';
        }
        table += '</thead>\n<tbody>';
        for (var key in tableData) {
            table += '<td style="'+cssStyle+'">'+tableData[key]+'</th>';
        }
        table += '</tbody>\n</table>\n';
        table += '<div class="template-element-orderTable-totals">\n';
        table += '<table><tbody>\n';
        table += '<tr><th>Sub Total</th><td>&pound;4.00</td></tr>\n';
        table += '<tr><th>VAT @20%</th><td>&pound;0.80</td></tr>\n';
        table += '<tr><th>Total</th><td>&pound;4.80</td></tr>\n';
        table += '</tbody></table>\n';
        table += '</div>'

        return table;
    };

    return new OrderTable();
});