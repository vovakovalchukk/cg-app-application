define(['InvoiceDesigner/Template/Element/TextAbstract'], function(TextAbstract)
{
    var Text = function()
    {
        TextAbstract.call(this);
        this.setType('Text');
    };

    Text.prototype = Object.create(TextAbstract.prototype);

    return Text;
});