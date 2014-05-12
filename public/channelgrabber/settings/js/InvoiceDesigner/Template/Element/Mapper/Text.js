define([
    'InvoiceDesigner/Template/Element/MapperAbstract'
], function(
    MapperAbstract
) {
    var Text = function()
    {
        MapperAbstract.call(this);
    };

    Text.attributePropertyMap = {
        fontColour: "color",
        align: "text-align"
    };

    Text.prototype = Object.create(MapperAbstract.prototype);

    Text.prototype.getExtraDomStyles = function(element)
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

    Text.prototype.getExtraAttributePropertyMap = function()
    {
        return Text.attributePropertyMap;
    };

    Text.prototype.getHtmlContents = function(element)
    {
        return element.getText().nl2br();
    };

    return new Text();
});