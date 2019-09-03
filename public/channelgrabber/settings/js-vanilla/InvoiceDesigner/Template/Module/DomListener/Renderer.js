define([
    'InvoiceDesigner/Template/Module/DomListenerAbstract',
    'jquery',
    'InvoiceDesigner/Template/DomManipulator',
    'InvoiceDesigner/Template/Element/Service',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/Module/DomListener/ElementManager'
], function(
    DomListenerAbstract,
    $,
    domManipulator,
    elementService,
    ElementMapperAbstract,
    ElementManager
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
            .initTemplateChangeListener()
            .initClickOutsideNonElementRelevantUIListener();
    };

    Renderer.prototype.initClickOutsideNonElementRelevantUIListener = function initElementClickListeners() {
        document.addEventListener('click', event => {
            if (isAnElementClick(event)) {
                return;
            }
            if (isAnInspectorClick(event) || isAnElementManagerClick(event)) {
                event.stopPropagation();
                return;
            }
            domManipulator.triggerElementDeletedEvent();
        });
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
            domManipulator.markAsInactive('.' + ElementMapperAbstract.ELEMENT_DOM_WRAPPER_CLASS);
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

    function isAnElementClick(event) {
        const elementClasses = '.' + elementService.getElementDomWrapperClass();
        return !!event.target.closest(elementClasses);
    }
    function isAnInspectorClick(event) {
        const inspectorArea = '#element-inspector-bar';
        return !!event.target.closest(inspectorArea);
    }
    function isAnElementManagerClick(event) {
        const elementManager = ElementManager.getSelector();
        return !!event.target.closest(elementManager);
    }
});