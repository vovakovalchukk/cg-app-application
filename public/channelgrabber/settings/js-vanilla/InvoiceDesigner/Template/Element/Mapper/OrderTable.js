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
        var tableStyles = [];
        var tableAttributes = ['backgroundColour', 'borderWidth', 'borderColour'];
        tableStyles = this.addOptionalDomStyles(element, tableAttributes, tableStyles);
        if (element.getBorderWidth()) {
            tableStyles.push('border-style: solid');
        }
        var cssStyle = tableStyles.join('; ');

        var templateUrl = MapperAbstract.ELEMENT_TEMPLATE_PATH + 'orderTable.mustache';
        var data = {
            tableStyles: cssStyle,
            tableHeaderStyles: cssStyle,
            tableDataStyles: cssStyle
        };
        var html = this.renderMustacheTemplate(templateUrl, data);
        return html;
    };

    OrderTable.prototype.createElement = function() {
        return new OrderTableElement();
    };

    return new OrderTable();
});