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

    Image.inspectableAttributes = [
        'source', 'format'
    ];

    Image.prototype = Object.create(ElementAbstract.prototype);

    Image.prototype.getInspectableAttributes = function()
    {
        var allAttributes = ElementAbstract.prototype.getInspectableAttributes.call(this);
        for(var key in Image.inspectableAttributes) {
            allAttributes.push(Image.inspectableAttributes[key]);
        }
        return allAttributes;
    };

    Image.prototype.toJson = function()
    {
        var json = ElementAbstract.prototype.toJson.call(this);
        var additional = {
            source: this.getSource(),
            format: this.getFormat()
        };
        for (var field in additional) {
            json[field] = additional[field];
        }

        return json;
    };

    return new Image();
});