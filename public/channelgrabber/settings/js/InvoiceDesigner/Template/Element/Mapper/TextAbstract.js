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
            extraDomStyles.push('font-size: '+element.getFontSize()+'pt');
        }
        var textAttribs = [
            'fontFamily', 'fontColour', 'padding', 'lineHeight', 'align', 'replacedText', 'removeBlankLines'
        ];
        extraDomStyles = this.addOptionalDomStyles(element, textAttribs, extraDomStyles);
        return extraDomStyles;
    };

    TextAbstract.prototype.getExtraAttributePropertyMap = function()
    {
        return TextAbstract.attributePropertyMap;
    };

    TextAbstract.prototype.getHtmlContents = function(element)
    {
        return element.getText().nl2br();
    };

    return TextAbstract;
});