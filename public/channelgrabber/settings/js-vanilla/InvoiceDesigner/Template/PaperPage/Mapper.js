define([
    'cg-mustache',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/Element/Helpers/Element'
], function(
    CGMustache,
    MapperAbstract,
    ElementHelper
) {
    var Mapper = function()
    {
        // Deliberately not extending MapperAbstract as that is for Elements which PaperPage is not,
        // just using it for its constants and static methods
        var cgMustache = new CGMustache();
        this.getCGMustache = function()
        {
            return cgMustache;
        };
    };

    Mapper.PAGE_DOM_CLASS = 'template-paperpage';

    Mapper.prototype.toHtml = function(paperPage)
    {
        var domId = ElementHelper.getElementDomId(paperPage);
        var cssClasses = this.getDomClasses(paperPage).join(' ');
        var cssStyle = this.getDomStyles(paperPage).join('; ');
        var htmlContents = paperPage.getHtmlContents();

        var templateUrl = MapperAbstract.ELEMENT_TEMPLATE_PATH+'page.mustache';
        var data = {
            id: domId,
            classes: cssClasses,
            styles: cssStyle,
            contents: htmlContents
        };
        var html = this.renderMustacheTemplate(templateUrl, data);

        return html;
    };

    Mapper.prototype.getDomClasses = function(paperPage)
    {
        var domClasses = [Mapper.PAGE_DOM_CLASS];
        return domClasses;
    };

    Mapper.prototype.getDomStyles = function(paperPage)
    {
        let measurementUnit = paperPage.getMeasurementUnit();
        var domStyles = [
            'width: '+paperPage.getWidth() + measurementUnit,
            'height: '+paperPage.getHeight() + measurementUnit
        ];
        return domStyles;
    };

    Mapper.prototype.renderMustacheTemplate = function(templateUrl, data)
    {
        var synchronous = true;
        var html;
        this.getCGMustache().fetchTemplate(templateUrl, function(template, cgMustache)
        {
            html = cgMustache.renderTemplate(template, data);
        }, synchronous);
        return html;
    };

    Mapper.prototype.getPageDomClass = function()
    {
        return Mapper.PAGE_DOM_CLASS;
    };

    return new Mapper();
});