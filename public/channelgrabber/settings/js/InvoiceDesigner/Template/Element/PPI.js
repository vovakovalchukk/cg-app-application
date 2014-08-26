define([
    'InvoiceDesigner/Template/ElementAbstract'
], function(
    ElementAbstract
) {
    var PPI = function(additionalData)
    {
        var data = {
            option: undefined
        };

        for (var field in additionalData) {
            data[field] = additionalData[field];
        };

        ElementAbstract.call(this, data);

        this.disableBaseInspectors(['backgroundColour', 'borderWidth', 'borderColour']);

        var sizeOptions = [
            {
                "name": "Small",
                "height": 14,
                "width": 35
            }, {
                "name": "Medium",
                "height": 14,
                "width": 54
            }, {
                "name": "Large",
                "height": 14,
                "width": 79.5
            }
        ];

        this.set('type', 'PPI', true);
        this.set('borderWidth', undefined, true);
        this.set('sizeOptions', sizeOptions, true);
        this.set('width', sizeOptions[0].width, true);
        this.set('height', sizeOptions[0].height, true);

        this.getOption = function()
        {
            return this.get('option');
        };

        this.setOption = function(newOption)
        {
            this.set('option', newOption);
            return this;
        };

        this.getSizeOptions = function()
        {
            return this.get('sizeOptions');
        };

        this.setSizeOptions = function(newSizeOptions)
        {
            this.set('sizeOptions', newSizeOptions);
            return this;
        };
    };

    PPI.prototype = Object.create(ElementAbstract.prototype);

    return PPI;
});