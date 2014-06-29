var jsImage = new Image();
define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var Image = function()
    {
        var additionalData = {
            source: undefined,
            format: undefined
        };

        ElementAbstract.call(this, additionalData);
        this.set('type', 'Image', true);

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

    Image.prototype.resizeImageData = function()
    {
        jsImage.src = 'data:image/' + this.getFormat().toLowerCase() + ';base64,' + this.getSource();
        var canvas = document.createElement('canvas'), ctx = canvas.getContext('2d');
        canvas.width = Number(this.getWidth()).mmToPx();
        canvas.height = Number(this.getHeight()).mmToPx();
        ctx.drawImage(jsImage, 0, 0, Number(this.getWidth()).mmToPx(), Number(this.getHeight()).mmToPx());
        return canvas.toDataURL().split(',')[1];
    };

    Image.prototype.toJson = function()
    {
        var json = ElementAbstract.prototype.toJson.call(this);
        json.source = this.resizeImageData();
        return json;
    };

    return Image;
});