define([
    'InvoiceDesigner/Template/Element/Mapper/TextAbstract',
    'InvoiceDesigner/Template/Element/Text'
], function(
    TextAbstract,
    TextElement
) {
    var SellerAddress = function()
    {
        TextAbstract.call(this);
    };

    SellerAddress.prototype = Object.create(TextAbstract.prototype);

    SellerAddress.prototype.createElement = function()
    {
        var element = new TextElement();
        var text = "{{organisationUnit.addressFullName}}\n{{organisationUnit.addressCompanyName}}\n{{organisationUnit.address1}}\n{{organisationUnit.address2}}\n{{organisationUnit.address3}}\n{{organisationUnit.addressCity}}\n{{organisationUnit.addressCounty}}\n{{organisationUnit.addressPostcode}}";
        return element
            .setWidth('100')
            .setHeight('40')
            .setText(text)
            .setRemoveBlankLines(true);
    };

    return new SellerAddress();
});