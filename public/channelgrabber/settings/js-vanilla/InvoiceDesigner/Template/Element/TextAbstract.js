define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var TextAbstract = function(additionalData)
    {
        var data = {
            height: 7,
            borderWidth: undefined,
            fontSize: 12,
            fontFamily: 'Arial',
            fontColour: 'black',
            text: 'Enter your text here.',
            padding: undefined,
            lineHeight: undefined,
            align: undefined,
            replacedText: undefined,
            removeBlankLines: undefined
        };

        for (var field in additionalData) {
            data[field] = additionalData[field];
        };

        ElementAbstract.call(this, data);

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

    TextAbstract.prototype.toJson = function()
    {
        var json = JSON.parse(JSON.stringify(ElementAbstract.prototype.toJson.call(this)));
        if (json.padding) {
            json.padding = Number(json.padding).mmToPt();
        }
        if (json.lineHeight) {
            json.lineHeight = Number(json.lineHeight).mmToPt(); 
        }
        return json;
    };

    return TextAbstract;
});
