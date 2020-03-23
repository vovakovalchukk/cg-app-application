define(['InvoiceDesigner/Template/Element/ImmutableTextAbstract'], function(ImmutableTextAbstract)
{
    var SellerAddress = function()
    {
        var data = {
            height: 60.5,
            text: "{{b}}Returns Address{{n}}\n{{organisationUnit.addressFullName}}\n{{organisationUnit.addressCompanyName}}\n{{organisationUnit.address1}}\n{{organisationUnit.address2}}\n{{organisationUnit.address3}}\n{{organisationUnit.addressCity}}\n{{organisationUnit.addressCounty}}\n{{organisationUnit.addressPostcode}}\n{{organisationUnit.addressCountry}}"
        };
        ImmutableTextAbstract.call(this, data);
        this.set('type', 'SellerAddress', true);
    };

    SellerAddress.prototype = Object.create(ImmutableTextAbstract.prototype);

    return SellerAddress;
});