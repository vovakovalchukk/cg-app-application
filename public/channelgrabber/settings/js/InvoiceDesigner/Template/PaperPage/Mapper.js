define([
    'cg-mustache',
    'InvoiceDesigner/Template/Element/MapperAbstract'
], function(
    CGMustache,
    MapperAbstract
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

    Mapper.prototype.toHtml = function(paperPage)
    {
        var domId = MapperAbstract.getDomId(paperPage);
        var cssClasses = this.getDomClasses(paperPage).join(' ');
        var cssStyle = this.getDomStyles(paperPage).join('; ');
        var htmlContents = paperPage.getHtmlContents();

        var templateUrl = MapperAbstract.ELEMENT_TEMPLATE_PATH+'abstract.mustache';
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
        var domClasses = ['template-paperpage'];
        return domClasses;
    };

    Mapper.prototype.getDomStyles = function(paperPage)
    {
        var domStyles = [
            'width: '+paperPage.getWidth()+'mm',
            'height: '+paperPage.getHeight()+'mm',
            'background: url('+paperPage.getBackgroundImage()+') no-repeat left top',
            'background-size: cover',
        ];
        return domStyles;
    };

    Mapper.prototype.renderMustacheTemplate = function(templateUrl, data)
    {
        var html;
        this.getCGMustache().fetchTemplate(templateUrl, function(template, cgMustache)
        {
            html = cgMustache.renderTemplate(template, data);
        });
        return html;
    };

    return new Mapper();
});