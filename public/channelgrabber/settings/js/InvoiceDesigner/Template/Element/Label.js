define([
    'InvoiceDesigner/Template/ElementAbstract',
    'InvoiceDesigner/Template/Element/SizeOptionsAbstract'
], function(
    ElementAbstract,
    SizeOptionsAbstract
) {
    function Label()
    {
        var data = {
            sizeOption: 1
        };
        var sizeIndex = parseInt(data.sizeOption) - 1;

        ElementAbstract.call(this, data);
        SizeOptionsAbstract.call(this);

        var init = function()
        {
            var sizeOptions = [{
                    "name": "6\" x 4\" (152mm x 101mm)",
                    "height": 101.6,
                    "width": 152.4
                }, {
                    "name": "4\" x 6\" (101mm x 152mm)",
                    "height": 152.4,
                    "width": 101.6
            }];

            this.disableBaseInspectors(['backgroundColour', 'borderWidth', 'borderColour']);
            this.set('type', 'Label', true);
            this.set('borderWidth', undefined, true);
            this.set('sizeOptions', sizeOptions, true);
            this.set('width', sizeOptions[sizeIndex].width, true);
            this.set('height', sizeOptions[sizeIndex].height, true);
            this.setResizable(false);
        };
        init.call(this);
    }

    Label.prototype = Object.create(ElementAbstract.prototype);
    for (var key in SizeOptionsAbstract.prototype) {
        if (typeof SizeOptionsAbstract.prototype[key] != 'function') {
            return;
        }
        Label.prototype[key] = SizeOptionsAbstract.prototype[key];
    }

    return Label;
});