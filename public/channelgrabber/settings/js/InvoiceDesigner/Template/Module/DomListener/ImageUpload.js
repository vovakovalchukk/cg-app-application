define([
    'InvoiceDesigner/Template/Module/DomListenerAbstract',
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    DomListenerAbstract,
    $,
    domManipulator
) {
    var ImageUpload = function()
    {
        DomListenerAbstract.call(this);
    };

    ImageUpload.prototype = Object.create(DomListenerAbstract.prototype);

    ImageUpload.prototype.init = function(module)
    {
        DomListenerAbstract.prototype.init.call(this, module);
        this.initListeners();
    };

    ImageUpload.prototype.initListeners = function()
    {
        var self = this;
        $(document).on(domManipulator.getImageUploadFileSelectedEvent(), function(event, elementDomId, file)
        {
            self.getModule().imageElementFileSelected(elementDomId, file);
        });
    };

    return new ImageUpload();
});