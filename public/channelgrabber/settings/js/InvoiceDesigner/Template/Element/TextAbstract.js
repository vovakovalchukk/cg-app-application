define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var TextAbstract = function()
    {
        var additionalData = {
            fontSize: undefined,
            fontFamily: undefined,
            fontColour: undefined,
            text: undefined,
            padding: undefined,
            lineHeight: undefined,
            align: undefined,
            replacedText: undefined,
            removeBlankLines: undefined
        };

        ElementAbstract.call(this, additionalData);

        this.getFontSize = function()
        {
            return this.get('fontSize');
        };

        this.setFontSize = function(newFontSize)
        {
            this.set('fontSize', newFontSize);
            return this;
        };

        this.getFontFamily = function()
        {
            return this.get('fontFamily');
        };

        this.setFontFamily = function(newFontFamily)
        {
            this.set('fontFamily', newFontFamily);
            return this;
        };

        this.getFontColour = function()
        {
            return this.get('fontColour');
        };

        this.setFontColour = function(newFontColour)
        {
            this.set('fontColour', newFontColour);
            return this;
        };

        this.getText = function()
        {
            return this.get('text');
        };

        this.setText = function(newText)
        {
            this.set('text', newText);
            return this;
        };

        this.getPadding = function()
        {
            return this.get('padding');
        };

        this.setPadding = function(newPadding)
        {
            this.set('padding', newPadding);
            return this;
        };

        this.getLineHeight = function()
        {
            return this.get('lineHeight');
        };

        this.setLineHeight = function(newLineHeight)
        {
            this.set('lineHeight', newLineHeight);
            return this;
        };

        this.getAlign = function()
        {
            return this.get('align');
        };

        this.setAlign = function(newAlign)
        {
            this.set('align', newAlign);
            return this;
        };

        this.getReplacedText = function()
        {
            return this.get('replacedText');
        };

        this.setReplacedText = function(newReplacedText)
        {
            this.set('replacedText', newReplacedText);
            return this;
        };

        this.getRemoveBlankLines = function()
        {
            return this.get('removeBlankLines');
        };

        this.setRemoveBlankLines = function(newRemoveBlankLines)
        {
            this.set('removeBlankLines', newRemoveBlankLines);
            return this;
        };
    };

    TextAbstract.prototype = Object.create(ElementAbstract.prototype);

    ElementAbstract.prototype.toJson = function()
    {
        var json = JSON.parse(JSON.stringify(ElementAbstract.getData()));
        json.padding = this.mmToPoints(json.padding);
        json.lineHeight = this.mmToPoints(json.lineHeight);
        return json;
    };

    return TextAbstract;
});