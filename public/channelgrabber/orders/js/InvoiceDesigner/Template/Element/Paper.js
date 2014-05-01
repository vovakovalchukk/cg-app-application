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

    Paper.inspectableAttributes = [
        'backgroundImage'
    ];

    Paper.prototype = Object.create(ElementAbstract.prototype);

    Paper.prototype.getInspectableAttributes = function()
    {
        var allAttributes = ElementAbstract.prototype.getInspectableAttributes.call(this);
        for(var key in Paper.inspectableAttributes) {
            allAttributes.push(Paper.inspectableAttributes[key]);
        }
        return allAttributes;
    };

    Paper.prototype.toJson = function()
    {
        var json = ElementAbstract.prototype.toJson.call(this);
        json.backgroundImage = this.getBackgroundImage();

        return json;
    };

    return new Paper();
});