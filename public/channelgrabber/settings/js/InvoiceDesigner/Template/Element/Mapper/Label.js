define([
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/Element/Label'
], function(
    MapperAbstract,
    LabelElement
) {
    function Label()
    {
        MapperAbstract.call(this);
    }

    Label.IMAGE_PATH = '/channelgrabber/settings/img/InvoiceDesigner/Template/Label/';

    Label.prototype = Object.create(MapperAbstract.prototype);

    Label.prototype.sizeOptionImages = {
        1: 'label6x4.jpg',
        2: 'label4x6.jpg'
    };

    Label.prototype.getImageForSizeOption = function(sizeOption)
    {
        if (!this.sizeOptionImages[sizeOption]) {
            return null;
        }
        return Label.IMAGE_PATH + this.sizeOptionImages[sizeOption];
    };

    Label.prototype.getHtmlContents = function(element)
    {
        var image = this.getImageForSizeOption(element.getSizeOption());
        if (!image) {
            return '';
        }

        var templateUrl = MapperAbstract.ELEMENT_TEMPLATE_PATH + 'label.mustache';
        var data = {
            'imgSrc': image,
            'width': element.getWidth(),
            'height': element.getHeight()
        };
        return this.renderMustacheTemplate(templateUrl, data);
    };

    Label.prototype.createElement = function()
    {
        return new LabelElement();
    };

    return new Label();
});