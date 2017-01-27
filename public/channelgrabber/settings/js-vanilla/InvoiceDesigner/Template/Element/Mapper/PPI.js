define([
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/Element/PPI'
], function(
    MapperAbstract,
    PPIElement
) {
    var PPI = function()
    {
        MapperAbstract.call(this);

        var images = {
            1: 'option-1.jpg',
            2: 'option-2.jpg',
            3: 'option-3.jpg',
            4: 'option-4.jpg'
        };

        this.getImage = function(option)
        {
            return images[option];
        };
    };

    PPI.IMAGE_PATH = '/channelgrabber/settings/img/InvoiceDesigner/Template/PPI/';

    PPI.prototype = Object.create(MapperAbstract.prototype);

    PPI.prototype.getHtmlContents = function(element)
    {
        var image = this.getImage(element.getOption());

        if (typeof(image) === 'undefined') {
            return '';
        }

        var templateUrl = MapperAbstract.ELEMENT_TEMPLATE_PATH + 'ppi.mustache';
        var data = {
            'imgSrc': PPI.IMAGE_PATH + image
        };

        return this.renderMustacheTemplate(templateUrl, data);
    };

    PPI.prototype.createElement = function()
    {
        return new PPIElement();
    };

    return new PPI();
});