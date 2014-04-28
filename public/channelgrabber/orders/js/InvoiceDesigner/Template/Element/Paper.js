define(['../ElementAbstract'], function(ElementAbstract)
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

    return new Paper();
});