define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/ElementManager',
    'InvoiceDesigner/Template/DomManipulator',
    'mustache',
    'cg-mustache',
    'jquery'
], function(
    ModuleAbstract,
    ElementManagerListener,
    domManipulator,
    Mustache,
    CGMustache,
    $
) {
    var ElementManager = function ()
    {
        var templateUrl = ModuleAbstract.TEMPLATE_PATH + 'buttons.mustache';
        var elementOptions = {
            buttons: [{
                value: 'Seller Address',
                icon: 'sprite-align-left-1520-black',
                element: 'SellerAddress'
            },{ value: 'Delivery Address',
                icon: 'sprite-align-left-1520-black',
                element: 'DeliveryAddress'
            },{ value: 'Image',
                icon: 'sprite-image-15-black',
                element: 'Image'
            },{ value: 'Text',
                icon: 'sprite-text-element-1520-black',
                element: 'Text'
            },{ value: 'Table',
                icon: 'sprite-order-table-1520-black',
                element: 'OrderTable'
            },{ value: 'Box',
                icon: 'sprite-box-15-black',
                element: 'Box'
            },{ value: 'PPI',
                icon: 'sprite-ppi-15-black',
                element: 'PPI'
            },{ value: 'Courier Label',
                icon: 'sprite-ppi-15-black',
                element: 'Label'
            },{ value: 'Barcode',
                icon: 'sprite-barcode-15-black',
                element: 'Barcode'
            }
        ]};

        ModuleAbstract.call(this);
        this.setDomListener(ElementManagerListener);

        this.getDomManipulator = function()
        {
            return domManipulator;
        };

        var init = function()
        {
            CGMustache.get().fetchTemplate(templateUrl, function(template) {
                var renderedTemplate = Mustache.render(template, elementOptions);
                $(ElementManagerListener.getContainerSelector()).append(renderedTemplate);
            });
        };
        init();
    };

    ElementManager.prototype = Object.create(ModuleAbstract.prototype);

    ElementManager.prototype.init = function(template, service)
    {
        ModuleAbstract.prototype.init.call(this, template, service);
        this.getDomManipulator().show(this.getDomListener().getContainerSelector());
    };

    ElementManager.prototype.addElementToCurrentTemplate = function(elementName)
    {
        var element = this.getTemplateService().getMapper().createNewElement(elementName);
        this.getTemplate().addElement(element);
        this.getDomManipulator().triggerElementSelectedEvent(element);
    };

    return new ElementManager();
});
