define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var TextAbstract = function()
    {
        ElementAbstract.call(this);

        var fontSize;
        var fontFamily;
        var fontColour;
        var text;
        var padding;
        var lineHeight;
        var align;
        var replacedText;
        var removeBlankLines;

        this.getFontSize = function()
        {
            return fontSize;
        };

        this.setFontSize = function(newFontSize)
        {
            fontSize = newFontSize;
            return this;
        };

        this.getFontFamily = function()
        {
            return fontFamily;
        };

        this.setFontFamily = function(newFontFamily)
        {
            fontFamily = newFontFamily;
            return this;
        };

        this.getFontColour = function()
        {
            return fontColour;
        };

        this.setFontColour = function(newFontColour)
        {
            fontColour = newFontColour;
            return this;
        };

        this.getText = function()
        {
            return text;
        };

        this.setText = function(newText)
        {
            text = newText;
            return this;
        };

        this.getPadding = function()
        {
            return padding;
        };

        this.setPadding = function(newPadding)
        {
            padding = newPadding;
            return this;
        };

        this.getLineHeight = function()
        {
            return lineHeight;
        };

        this.setLineHeight = function(newLineHeight)
        {
            lineHeight = newLineHeight;
            return this;
        };

        this.getAlign = function()
        {
            return align;
        };

        this.setAlign = function(newAlign)
        {
            align = newAlign;
            return this;
        };

        this.getReplacedText = function()
        {
            return replacedText;
        };

        this.setReplacedText = function(newReplacedText)
        {
            replacedText = newReplacedText;
            return this;
        };

        this.getRemoveBlankLines = function()
        {
            return removeBlankLines;
        };

        this.setRemoveBlankLines = function(newRemoveBlankLines)
        {
            removeBlankLines = newRemoveBlankLines;
            return this;
        };
    };

    TextAbstract.inspectableAttributes = [
        'fontSize', 'fontFamily', 'fontColour', 'text', 'padding', 'lineHeight', 'align', 'replacedText', 'removeBlankLines'
    ];

    TextAbstract.prototype = Object.create(ElementAbstract.prototype);

    TextAbstract.prototype.getInspectableAttributes = function()
    {
        var allAttributes = ElementAbstract.prototype.getInspectableAttributes.call(this);
        for(var key in TextAbstract.inspectableAttributes) {
            allAttributes.push(TextAbstract.inspectableAttributes[key]);
        }
        return allAttributes;
    };

    TextAbstract.prototype.toJson = function()
    {
        var json = ElementAbstract.prototype.toJson.call(this);
        var additional = {
            fontSize: this.getFontSize(),
            fontFamily: this.getFontFamily(),
            fontColour: this.getFontColour(),
            text: this.getText(),
            padding: this.getPadding(),
            lineHeight: this.getLineHeight(),
            align: this.getAlign(),
            replacedText: this.getReplacedText(),
            removeBlankLines: this.getRemoveBlankLines()
        };
        for (var field in additional) {
            json[field] = additional[field];
        }

        return json;
    };

    return TextAbstract;
});