define([
    'InvoiceDesigner/Template/Element/MapperAbstract'
], function(
    MapperAbstract
) {
    var Page = function()
    {
        MapperAbstract.call(this);
    };

    Page.prototype = Object.create(MapperAbstract.prototype);

    Page.prototype.getExtraDomStyles = function(element)
    {
        return ['background: url('+element.getBackgroundImage()+') no-repeat left top'];
    };

    Page.prototype.getHtmlContents = function(element)
    {
        return element.getContent();
    };

    return new Page();
});