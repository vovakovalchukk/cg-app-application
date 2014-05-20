define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var Image = function()
    {
        var additionalData = {
            source: undefined,
            format: undefined
        };

        ElementAbstract.call(this, additionalData);
        this.setType('Image');

        this.getSource = function()
        {
            return this.get('source');
        };

        this.setSource = function(newSource)
        {
            this.set('source', newSource);
            return this;
        };

        this.getFormat = function()
        {
            return this.get('format');
        };

        this.setFormat = function(newFormat)
        {
            this.set('format', newFormat);
            return this;
        };
    };

    Image.prototype = Object.create(ElementAbstract.prototype);

    return Image;
});