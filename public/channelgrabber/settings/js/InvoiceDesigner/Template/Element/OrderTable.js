define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var OrderTable = function()
    {
        ElementAbstract.call(this);
        this.setType('OrderTable');
    };

    OrderTable.prototype = Object.create(ElementAbstract.prototype);

    return OrderTable;
});