define([
    'InvoiceDesigner/Template/Element/Mapper/TextAbstract'
], function(
    TextAbstract
) {
    var SellerAddress = function()
    {
        TextAbstract.call(this);
    };

    SellerAddress.prototype = Object.create(TextAbstract.prototype);

    return new SellerAddress();
});