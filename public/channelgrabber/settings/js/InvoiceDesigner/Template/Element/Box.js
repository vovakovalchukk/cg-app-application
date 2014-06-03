define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var Box = function()
    {
        ElementAbstract.call(this);
        this.set('type', 'Box', true);
    };

    Box.prototype = Object.create(ElementAbstract.prototype);

    return Box;
});