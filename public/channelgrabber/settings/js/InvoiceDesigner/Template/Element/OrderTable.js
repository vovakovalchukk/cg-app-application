define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var OrderTable = function()
    {
        var elementWidth = 700;
        ElementAbstract.call(this);
        this.set('type', 'OrderTable', true);
        this.setMinWidth(elementWidth)
            .setMaxWidth(elementWidth);
    };

    OrderTable.prototype = Object.create(ElementAbstract.prototype);

    return OrderTable;
});