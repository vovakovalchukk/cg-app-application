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
        this.initElementSelectedListener()
            .initElementDeselectedListener()
            .initTemplateChangeListener();
    };

    Renderer.prototype.initElementSelectedListener = function()
    {
        var self = this;
        $(document).on(domManipulator.getElementSelectedEvent(), function(event, element)
        {
            self.getModule().elementSelected(element);
        });
        return this;
    };

    Renderer.prototype.initElementDeselectedListener = function()
    {
        var self = this;
        $(document).on(domManipulator.getElementDeselectedEvent(), function(event, element)
        {
            self.getModule().elementDeselected(element);
        });
        return this;
    };

    Renderer.prototype.initTemplateChangeListener = function()
    {
        var self = this;

        $(document).on(domManipulator.getTemplateChangedEvent(), function(event, template, performedUpdates)
        {
            self.getModule().templateChanged(template, performedUpdates);
        });
        return this;
    };

    Renderer.prototype.listenForElementSelect = function(domId, element)
    {
        var self = this;
        $('#'+domId).off('mousedown focus').on('mousedown focus', function()
        {
            domManipulator.triggerElementSelectedEvent(element);
        });
    };

    return new Renderer();
});