define(['InvoiceDesigner/Template/DomManipulator'], function(domManipulator)
{
    var InspectorAbstract = function()
    {
        var id;
        var inspectedAttributes = [];
        var template;

        this.getId = function()
        {
            return id;
        };

        this.setId = function(newId)
        {
            id = newId;
        };

        this.getInspectedAttributes = function()
        {
            return inspectedAttributes;
        };

        this.setInspectedAttributes = function(newInspectedAttributes)
        {
            inspectedAttributes = newInspectedAttributes;
        };

        this.getTemplate = function()
        {
            return template;
        };

        this.setTemplate = function(newTemplate)
        {
            template = newTemplate;
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    /*
     * Sub-classes should, in their constructor:
     * call this.setId() with a sensible name for the inspector e.g. 'text', 'border', etc
     * call this.setInspectedAttributes() with an array of attributes it can inspect
     */

    InspectorAbstract.prototype.init = function(template)
    {
        this.setTemplate(template);
        // Sub-classes should override with a .call() to this method then do their own work
    };

    /**
     * @abstract
     */
    InspectorAbstract.prototype.hide = function()
    {
        throw 'RuntimeException: InvoiceDesigner\Template\InspectorAbstract::clear() should be overridden by sub-class';
    };

    /**
     * @abstract
     */
    InspectorAbstract.prototype.showForElement = function(element)
    {
        throw 'RuntimeException: InvoiceDesigner\Template\InspectorAbstract::showForElement() should be overridden by sub-class';
    };

    return InspectorAbstract;
});