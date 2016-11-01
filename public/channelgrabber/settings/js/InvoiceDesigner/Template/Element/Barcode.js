define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var Barcode = function()
    {
        ElementAbstract.call(this);
        this.set('type', 'Barcode', true);
        this.set('width', 54, true);
        this.set('height', 35, true);
        this.setResizable(false);
    };

    Barcode.prototype = Object.create(ElementAbstract.prototype);

    return Barcode;
});