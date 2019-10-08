define([
    'InvoiceDesigner/Module/DomListenerAbstract',
    'jquery'
], function(
    DomListenerAbstract,
    $
) {

    var ElementManager = function()
    {
        DomListenerAbstract.call(this);
    };

    ElementManager.SELECTOR = '.addInvoiceElement div.button';
    ElementManager.CONTAINER_SELECTOR = '.addInvoiceElement';

    ElementManager.prototype = Object.create(DomListenerAbstract.prototype);

    ElementManager.prototype.init = function(module)
    {
        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);
        $(ElementManager.SELECTOR).off('click').click(function (event) {
            var elementType = $(this).data('element');
            self.getModule().addElementToCurrentTemplate(elementType, event);
        });
    };

    ElementManager.prototype.getSelector = function()
    {
        return ElementManager.SELECTOR;
    };

    ElementManager.prototype.getContainerSelector = function()
    {
        return ElementManager.CONTAINER_SELECTOR;
    };

    return new ElementManager();
});