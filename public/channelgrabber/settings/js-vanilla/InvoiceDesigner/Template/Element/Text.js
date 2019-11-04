define([
    'InvoiceDesigner/Template/Element/TextAbstract'
], function(
    TextAbstract
) {
    var Text = function()
    {
        TextAbstract.call(this);
        this.set('displayOnAllPages', false);
        this.set('type', 'Text', true);
    };

    Text.prototype = Object.create(TextAbstract.prototype);

    return Text;
});