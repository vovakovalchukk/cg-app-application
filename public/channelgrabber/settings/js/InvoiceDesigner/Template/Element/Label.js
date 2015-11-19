define([
    'InvoiceDesigner/Template/ElementAbstract'
], function(
    ElementAbstract
) {
    var Label = function()
    {
        var data = {
            sizeOption: 1
        };
        var sizeIndex = parseInt(data.sizeOption) - 1;

        ElementAbstract.call(this, data);

        this.getSizeOption = function()
        {
            return this.get('sizeOption');
        };

        this.setSizeOption = function(newOption)
        {
            this.set('sizeOption', parseInt(newOption));
            return this;
        };

        var init = function()
        {
            this.set('type', 'Label', true);
            this.set('borderWidth', undefined, true);
            this.set('sizeOptions', this.sizeOptions, true);
            this.set('width', this.sizeOptions[sizeIndex].width, true);
            this.set('height', this.sizeOptions[sizeIndex].height, true);
            this.setResizable(false);
        };
        init.call(this);
    };

    Label.prototype = Object.create(ElementAbstract.prototype);

    // Names are in inches but actual dimensions are in mm
    Label.prototype.sizeOptions = [{
            "name": "6\" x 4\"",
            "height": 101.6,
            "width": 152.4
        }, {
            "name": "4\" x 6\"",
            "height": 152.4,
            "width": 101.6
    }];

    return Label;
});