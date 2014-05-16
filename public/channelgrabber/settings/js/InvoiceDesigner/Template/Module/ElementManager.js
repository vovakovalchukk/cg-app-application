define([
    'InvoiceDesigner/Template/Entity',

    'InvoiceDesigner/Template/Element/SellerAddress',
    'InvoiceDesigner/Template/Element/DeliveryAddress',
    'InvoiceDesigner/Template/Element/Image',
    'InvoiceDesigner/Template/Element/Text',
    'InvoiceDesigner/Template/Element/OrderTable',
    'InvoiceDesigner/Template/Element/Box',
], function(
    TemplateEntity,
    SellerAddressElement,
    DeliveryAddress,
    Image,
    Text,
    OrderTable,
    Box
    ) {

    var ElementManager = function ()
    {
        var init = function()
        {
            clickListener();
        };

        var clickListener = function()
        {
            $(document).on('click', '#invoice-controls-bar .addElements div.button', function() {
                var element = $(this).data('element');
                TemplateEntity.addElement(create(element), true);
            });
        };

        var create = function(elementName)
        {
            if (elementName == 'SellerAddress') {
                return new SellerAddress();
            } else if (elementName == 'DeliveryAddress')  {
                return new DeliveryAddress();
            } else if (elementName == 'Image')  {
                return new Image();
            } else if (elementName == 'Text')  {
                return new Text();
            } else if (elementName == 'OrderTable')  {
                return new OrderTable();
            } else if (elementName == 'Box')  {
                return new Box();
            }
        };

        init();
    };

    return ElementManager;
});