define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var Box = function()
    {
        ElementAbstract.call(this);
        this.setType('Box');
    };

    Box.prototype = Object.create(ElementAbstract.prototype);

    return Box;
});