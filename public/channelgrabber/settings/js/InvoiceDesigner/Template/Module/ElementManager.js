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
                element: 'SellerAddress'
            },{ value: 'Delivery Address',
                element: 'DeliveryAddress'
            },{ value: 'Image',
                element: 'Image'
            },{ value: 'Text',
                element: 'Text'
            },{ value: 'Order Table',
                element: 'OrderTable'
            },{ value: 'Box',
                element: 'Box'
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