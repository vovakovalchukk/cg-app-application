define(['../ElementAbstract'], function(ElementAbstract)
{
    var Image = function()
    {
        ElementAbstract.call(this);

        var source;
        var format;

        this.getSource = function()
        {
            return source;
        };

        this.setSource = function(newSource)
        {
            source = newSource;
            return this;
        };

        this.getFormat = function()
        {
            return format;
        };

        this.setFormat = function(newFormat)
        {
            format = newFormat;
            return this;
        };
    };

    Image.prototype = Object.create(ElementAbstract.prototype);

    return new Image();
});