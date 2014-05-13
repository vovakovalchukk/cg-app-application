define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var Page = function()
    {
        var content;
        var additionalData = {
            backgroundImage: undefined
        };

        ElementAbstract.call(this, additionalData);

        this.getBackgroundImage = function()
        {
            return this.get('backgroundImage');
        };

        this.setBackgroundImage = function(newBackgroundImage)
        {
            this.set('backgroundImage', newBackgroundImage);
            return this;
        };

        this.setContent = function(newContent)
        {
            content = newContent;
            return this;
        };

        this.getContent = function()
        {
            return content;
        };
    };

    Page.prototype = Object.create(ElementAbstract.prototype);

    Page.prototype.toJson = function()
    {
        var json = ElementAbstract.prototype.toJson.call(this);
        json.backgroundImage = this.getBackgroundImage();

        return json;
    };

    return Page;
});