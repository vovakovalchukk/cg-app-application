define([
    'InvoiceDesigner/Template/Element/MapperAbstract'
], function(
    MapperAbstract
) {
    var TextAbstract = function()
    {
        MapperAbstract.call(this);
    };

    TextAbstract.attributePropertyMap = {
        fontColour: "color",
        align: "text-align"
    };

    TextAbstract.prototype = Object.create(MapperAbstract.prototype);

    TextAbstract.prototype.getExtraDomStyles = function(element)
    {
        var extraDomStyles = [];
        if (element.getFontSize()) {
            extraDomStyles.push('font-size: ' + element.getFontSize()+'pt');
        }

        extraDomStyles.push('font-family: ' + this.getFont(element.getFontFamily()));

        var textAttribs = [
            'fontColour', 'padding', 'lineHeight', 'align', 'replacedText', 'removeBlankLines'
        ];
        extraDomStyles = this.addOptionalDomStyles(element, textAttribs, extraDomStyles);
        return extraDomStyles;
    };

    TextAbstract.prototype.getFont = function(font)
    {
        if (font == 'TimesRoman') {
            return '"Times New Roman"';
        }
        return font;
    };

    TextAbstract.prototype.getExtraAttributePropertyMap = function()
    {
        return TextAbstract.attributePropertyMap;
    };

    TextAbstract.prototype.getHtmlContents = function(element)
    {
        return this.convertInlineStyleTagsToHtml(element.getText()).nl2br();
    };

    TextAbstract.prototype.convertInlineStyleTagsToHtml = function(rawHtml)
    {
        var html = '<span>'+rawHtml.replace(/{{([a-z]+)}}/gi, '</span><span class="style-tag-$1">')+'</span>';
        return html;
    };

    return TextAbstract;
});