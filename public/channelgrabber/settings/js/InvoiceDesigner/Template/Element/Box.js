define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var Box = function()
    {
        ElementAbstract.call(this);
    };

    Box.prototype = Object.create(ElementAbstract.prototype);

    return Box;
});