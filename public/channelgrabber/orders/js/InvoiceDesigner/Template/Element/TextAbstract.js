define(['../ElementAbstract'], function(ElementAbstract)
{
    var TextAbstract = function()
    {
        ElementAbstract.call(this);

        var fontSize;
        var fontFamily;
        var fontColour;
        var textabstract;
        var padding;
        var lineHeight;
        var align;
        var replacedTextAbstract;
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

        this.getTextAbstract = function()
        {
            return textabstract;
        };

        this.setTextAbstract = function(newTextAbstract)
        {
            textabstract = newTextAbstract;
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

        this.getReplacedTextAbstract = function()
        {
            return replacedTextAbstract;
        };

        this.setReplacedTextAbstract = function(newReplacedTextAbstract)
        {
            replacedTextAbstract = newReplacedTextAbstract;
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

    TextAbstract.prototype = Object.create(ElementAbstract.prototype);

    return TextAbstract;
});