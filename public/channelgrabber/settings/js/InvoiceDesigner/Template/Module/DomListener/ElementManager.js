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

    ElementManager.SELECTOR = '.addElements div.button';

    ElementManager.prototype = Object.create(DomListenerAbstract.prototype);

    ElementManager.prototype.init = function(module)
    {
        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);
        $(ElementManager.SELECTOR).off('click').click(function () {
            var elementType = $(this).data('element');
            self.getModule().addElementToCurrentTemplate(elementType);
        });
    };

    return new ElementManager();
});