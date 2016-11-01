define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var Barcode = function()
    {
        ElementAbstract.call(this);
        this.set('type', 'Barcode', true);
        this.set('width', 53, true);
        this.set('height', 33, true);
        this.set('borderWidth', undefined, true);
        this.setResizable(false);
    };

    Barcode.prototype = Object.create(ElementAbstract.prototype);

    return Barcode;
});