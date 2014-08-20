define(['InvoiceDesigner/Template/Element/MapperAbstract'], function(MapperAbstract) {
    var PPI = function() {
        MapperAbstract.call(this);
    };

    PPI.IMAGE_PATH = '/channelgrabber/settings/img/InvoiceDesigner/Template/PPI/';

    PPI.prototype = Object.create(MapperAbstract.prototype);

    var images = {
        1: 'option-1.jpg',
        2: 'option-2.jpg',
        3: 'option-3.jpg',
        4: 'option-4.jpg'
    };

    PPI.prototype.getHtmlContents = function(element) {
        var image = images[element.getOption()];

        if (typeof(image) === 'undefined') {
            return '';
        }

        var templateUrl = MapperAbstract.ELEMENT_TEMPLATE_PATH + 'ppi.mustache';
        var data = {
            'imgSrc': PPI.IMAGE_PATH + image
        };

        return this.renderMustacheTemplate(templateUrl, data);
    };

    return new PPI();
});