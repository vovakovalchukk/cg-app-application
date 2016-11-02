define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var Barcode = function()
    {
        var data = {
            action: 'id'
        };

        ElementAbstract.call(this, data);

        this.getAction = function()
        {
            return this.get('action');
        };

        this.setAction = function(newAction)
        {
            this.set('action', newAction);
            return this;
        };

        this.set('type', 'Barcode', true);
        this.set('width', 53, true);
        this.set('height', 33, true);
        this.set('borderWidth', undefined, true);
        this.setResizable(false);
        this.disableBaseInspectors(['backgroundColour', 'borderWidth', 'borderColour']);
    };

    Barcode.prototype = Object.create(ElementAbstract.prototype);

    return Barcode;
});