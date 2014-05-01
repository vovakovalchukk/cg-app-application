define(['../ElementAbstract.js'], function(ElementAbstract)
{
    var Paper = function()
    {
        ElementAbstract.call(this);

        var backgroundImage;

        this.getBackgroundImage = function()
        {
            return backgroundImage;
        };

        this.setBackgroundImage = function(newBackgroundImage)
        {
            backgroundImage = newBackgroundImage;
            return this;
        };
    };

    Paper.prototype = Object.create(ElementAbstract.prototype);

    Paper.prototype.toJson = function()
    {
        var json = ElementAbstract.prototype.toJson.call(this);
        json.backgroundImage = this.getBackgroundImage();

        return json;
    };

    return new Paper();
});