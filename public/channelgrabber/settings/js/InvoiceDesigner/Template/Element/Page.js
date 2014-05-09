define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var Page = function()
    {
        ElementAbstract.call(this);

        var backgroundImage;

        var extraInspectableAttributes = [
            'backgroundImage'
        ];

        this.getBackgroundImage = function()
        {
            return backgroundImage;
        };

        this.setBackgroundImage = function(newBackgroundImage)
        {
            backgroundImage = newBackgroundImage;
            return this;
        };

        this.getExtraInspectableAttributes = function() {
            return extraInspectableAttributes;
        };
    };

    Page.prototype = Object.create(ElementAbstract.prototype);

    Page.prototype.toJson = function()
    {
        var json = ElementAbstract.prototype.toJson.call(this);
        json.backgroundImage = this.getBackgroundImage();

        return json;
    };

    return new Page();
});