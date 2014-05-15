define([
    'InvoiceDesigner/Template/Module/DomListenerAbstract',
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    DomListenerAbstract,
    $,
    domManipulator
) {
    var Renderer = function()
    {
        DomListenerAbstract.call(this);
    };

    Renderer.prototype = Object.create(DomListenerAbstract.prototype);

    Renderer.prototype.init = function(module)
    {
        DomListenerAbstract.prototype.init.call(this, module);
        this.initListeners();
    };

    Renderer.prototype.initListeners = function()
    {
        var self = this;
        $(document).on(domManipulator.getImageUploadFileSelectedEvent(), function(event, elementDomId, file)
        {
            self.getModule().imageElementFileSelected(elementDomId, file);
        });
    };

    return new Renderer();
});