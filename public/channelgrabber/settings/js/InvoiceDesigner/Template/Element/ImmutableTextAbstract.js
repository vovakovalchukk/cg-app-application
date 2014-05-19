define(['InvoiceDesigner/Template/Element/TextAbstract'], function(TextAbstract)
{
    var ImmutableTextAbstract = function()
    {
        TextAbstract.call(this);

        this.setEditable(false);
    };

    ImmutableTextAbstract.prototype = Object.create(TextAbstract.prototype);

    ImmutableTextAbstract.prototype.toJson = function()
    {
        var json = JSON.parse(JSON.stringify(TextAbstract.prototype.toJson.call(this)));
        delete json.text;
        return json;
    };

    return ImmutableTextAbstract;
});