define([
    'InvoiceDesigner/Template/Element/Mapper/TextAbstract',
    'InvoiceDesigner/Template/Element/Text'
], function(
    TextAbstract,
    TextElement
) {
    var Text = function()
    {
        TextAbstract.call(this);
    };

    Text.prototype = Object.create(TextAbstract.prototype);

    Text.prototype.createElement = function()
    {
        return new TextElement();
    };

    return new Text();
});