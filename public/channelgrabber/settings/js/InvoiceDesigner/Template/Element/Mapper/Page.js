define([
    'InvoiceDesigner/Template/Element/MapperAbstract'
], function(
    MapperAbstract
) {
    var Page = function()
    {
        MapperAbstract.call(this);

        var pageContent;

        this.setPageContent = function(newPageContent)
        {
            pageContent = newPageContent;
            return this;
        };

        this.getPageContent = function()
        {
            return pageContent;
        };
    };

    Page.prototype = Object.create(MapperAbstract.prototype);

    MapperAbstract.prototype.getExtraDomStyles = function(element)
    {
        return ['background: url('+element.getBackgroundImage()+') no-repeat left top'];
    };

    MapperAbstract.prototype.getHtmlContents = function(element)
    {
        return this.getPageContent();
    };

    return new Page();
});