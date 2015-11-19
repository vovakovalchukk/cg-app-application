define([
    'InvoiceDesigner/Template/ElementAbstract',
    'InvoiceDesigner/Template/Element/SizeOptionsAbstract'
], function(
    ElementAbstract,
    SizeOptionsAbstract
) {
    var PPI = function(additionalData)
    {
        var data = {
            option: 1
        };

        for (var field in additionalData) {
            data[field] = additionalData[field];
        };

        ElementAbstract.call(this, data);

        this.disableBaseInspectors(['backgroundColour', 'borderWidth', 'borderColour']);

        var sizeIndex = parseInt(data.option) - 1;
        var sizeOptions = [
            {
                "name": "14mm",
                "height": 14,
                "width": 79.5
            }, {
                "name": "22mm",
                "height": 22,
                "width": 117
            }, {
                "name": "26mm",
                "height": 26,
                "width": 136
            }, {
                "name": "30mm",
                "height": 30,
                "width": 106.5
            }
        ];

        this.set('type', 'PPI', true);
        this.set('borderWidth', undefined, true);
        this.set('sizeOptions', sizeOptions, true);
        this.set('width', sizeOptions[sizeIndex].width, true);
        this.set('height', sizeOptions[sizeIndex].height, true);
        this.setResizable(false);

        this.getOption = function()
        {
            return this.get('option');
        };

        this.setOption = function(newOption)
        {
            this.set('option', parseInt(newOption));
            return this;
        };
    };

    PPI.prototype = Object.create(ElementAbstract.prototype);
    for (var key in SizeOptionsAbstract.prototype) {
        if (typeof SizeOptionsAbstract.prototype[key] != 'function') {
            return;
        }
        PPI.prototype[key] = SizeOptionsAbstract.prototype[key];
    }

    PPI.prototype.getSizeOption = function()
    {
        return this.getOption();
    };

    PPI.prototype.setSizeOption = function(newSizeOption)
    {
        this.setOption(newSizeOption);
        return this;
    };

    return PPI;
});