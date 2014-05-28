define([
    'InvoiceDesigner/Template/Element/MapperAbstract'
], function(
    MapperAbstract
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

    return new Image();
});