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
        var encodedSource = btoa(element.getSource());
        return 'data:image/'+element.getFormat()+';base64,'+encodedSource;
    };

    return new Image();
});