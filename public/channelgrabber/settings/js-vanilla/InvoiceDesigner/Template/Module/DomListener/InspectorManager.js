define([
    'InvoiceDesigner/Template/Module/DomListenerAbstract',
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    DomListenerAbstract,
    $,
    domManipulator
) {
    var InspectorManager = function()
    {
        DomListenerAbstract.call(this);
    };

    InspectorManager.prototype = Object.create(DomListenerAbstract.prototype);

    InspectorManager.prototype.init = function(module)
    {
        DomListenerAbstract.prototype.init.call(this, module);
        this.initElementSelectedListener();
        this.initElementDeselectedListener();
        this.initTableCellClickListener();
    };

    InspectorManager.prototype.initElementSelectedListener = function()
    {
        var self = this;

        $(document).off(domManipulator.getElementSelectedEvent()).on(domManipulator.getElementSelectedEvent(), function(event, element, event)
        {
            self.getModule().elementSelected(element, event);
        });
    };

    InspectorManager.prototype.initElementDeselectedListener = function()
    {
        var self = this;
        $(document).off(domManipulator.getElementDeselectedEvent()).on(domManipulator.getElementDeselectedEvent(), function(event, element)
        {
            self.getModule().elementDeselected(element);
        });
    };

    InspectorManager.prototype.initTableCellClickListener = function() {
        document.addEventListener("tableCellClick", event => {
            this.getModule().tableCellClick(event);
        }, false);
    };

    return new InspectorManager();
});