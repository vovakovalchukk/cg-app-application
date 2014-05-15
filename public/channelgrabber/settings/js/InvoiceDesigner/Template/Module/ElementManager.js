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
    SellerAddress,
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
                console.log(TemplateEntity.getElements().getItems());
            });
        };

        var create = function(elementName)
        {
            elementName = elementName.toLowerCase();
            if (elementName == 'selleraddress') {
                return new SellerAddress();
            } else if (elementName == 'deliveryaddress')  {
                return new DeliveryAddress();
            } else if (elementName == 'image')  {
                return new Image();
            } else if (elementName == 'text')  {
                return new Text();
            } else if (elementName == 'ordertable')  {
                return new OrderTable();
            } else if (elementName == 'box')  {
                return new Box();
            }
        };

        init();
    };

    return ElementManager;
});