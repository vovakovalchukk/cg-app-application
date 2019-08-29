define([
    'InvoiceDesigner/Template/Module/DomListenerAbstract',
    'jquery',
    'InvoiceDesigner/Template/DomManipulator',
    'InvoiceDesigner/Template/Element/Service'
], function(
    DomListenerAbstract,
    $,
    domManipulator,
    elementService
) {
    var Renderer = function()
    {
        DomListenerAbstract.call(this);
    };

    Renderer.prototype = Object.create(DomListenerAbstract.prototype);

    Renderer.prototype.init = function(module)
    {
        console.log('in renderer init');
        
        
        DomListenerAbstract.prototype.init.call(this, module);
        this.initElementDeselectedListener();
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
        $(document).on(domManipulator.getElementDeselectedEvent(), (event, element) =>
        {
            this.getModule().elementDeselected(element);
        });

        document.addEventListener('click', event => {
            const elementClasses = '.' + elementService.getElementDomWrapperClass();
            if (event.target.closest(elementClasses)) {
                return;
            }
            this.getModule().elementDeselected();
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