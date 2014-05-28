define([
    'InvoiceDesigner/Module/DomListenerAbstract',
    'jquery'
], function(
    DomListenerAbstract,
    $
) {

    var ToPdf = function()
    {
        DomListenerAbstract.call(this);
    };

    ToPdf.BUTTON_SELECTOR = '.toPdfButton';

    ToPdf.prototype = Object.create(DomListenerAbstract.prototype);

    ToPdf.prototype.init = function(module)
    {
        DomListenerAbstract.prototype.init.call(this, module);
        var self = this;
        $(ToPdf.BUTTON_SELECTOR).click(function() {
            self.getModule().toPdf();
        });
    };

    return new ToPdf();
});