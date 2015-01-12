define([
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/Element/Image'
], function(
    MapperAbstract,
    ImageElement
) {
    var Image = function()
    {
        MapperAbstract.call(this);
    };

    Image.prototype = Object.create(MapperAbstract.prototype);

    Image.prototype.getHtmlContents = function(element)
    {
        var templateUrl = MapperAbstract.ELEMENT_TEMPLATE_PATH+'image.mustache';
        var data = {
            id: MapperAbstract.getDomId(element)
        };
        if (element.getSource()) {
            data.imgSrc = this.elementSourceToImageData(element);
        }

        var html = this.renderMustacheTemplate(templateUrl, data);
        return html;
    };

    Image.prototype.elementSourceToImageData = function(element)
    {
        return 'data:image/'+element.getFormat().toLowerCase()+';base64,'+element.getSource();
    };

    Image.prototype.createElement = function()
    {
        return new ImageElement();
    };

    return new Image();
});