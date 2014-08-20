define(['InvoiceDesigner/Template/Element/MapperAbstract'], function(MapperAbstract) {
    var PPI = function() {
        MapperAbstract.call(this);
    };

    PPI.prototype = Object.create(MapperAbstract.prototype);

    var images = {

    };

    PPI.prototype.getHtmlContents = function(element) {
        var image = images[element.getOption()];

        if (typeof(image) === 'undefined') {
            return '';
        }

        var templateUrl = MapperAbstract.ELEMENT_TEMPLATE_PATH + 'ppi.mustache';
        var data = {
            'imgSrc': image
        };

        return this.renderMustacheTemplate(templateUrl, data);
    };

    return new PPI();
});