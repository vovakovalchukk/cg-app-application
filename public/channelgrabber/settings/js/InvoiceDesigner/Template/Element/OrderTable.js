define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var OrderTable = function()
    {
        var elementWidth = 700; // px
        var minHeight = 200; // px

        ElementAbstract.call(this);
        this.set('type', 'OrderTable', true);
        this.setWidth(elementWidth.pxToMm())
            .setHeight(minHeight.pxToMm())
            .setMinWidth(elementWidth)
            .setMaxWidth(elementWidth)
            .setMinHeight(minHeight);
    };

    OrderTable.prototype = Object.create(ElementAbstract.prototype);

    return OrderTable;
});