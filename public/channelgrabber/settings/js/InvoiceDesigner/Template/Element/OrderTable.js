define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var OrderTable = function()
    {
        ElementAbstract.call(this);
        this.set('type', 'OrderTable', true);
    };

    OrderTable.prototype = Object.create(ElementAbstract.prototype);

    return OrderTable;
});