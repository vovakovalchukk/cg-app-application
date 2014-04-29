define(function()
{
    var InspectorAbstract = function()
    {
        var id;
        var supportedTypes = [];
        var template;

        this.getId = function()
        {
            return id;
        };

        this.setId = function(newId)
        {
            id = newId;
        };

        this.getSupportedTypes = function()
        {
            return supportedTypes;
        };

        this.setSupportedTypes = function(newSupportedTypes)
        {
            supportedTypes = newSupportedTypes;
        };

        this.getTemplate = function()
        {
            return template;
        };

        this.setTemplate = function(newTemplate)
        {
            template = newTemplate;
        };
    };

    InspectorAbstract.prototype.init = function(template)
    {
        this.setTemplate(template);
        // Sub-classes should override with a .call() to this method then do their own work
    };

    /*
     * Sub-classes should, in their constructor:
     * call this.setId() with a sensible name for the inspector e.g. 'text', 'border', etc
     * call this.setSupportedTypes() with an array of supported types
     *
     * Sub-classes should implement:
     * clear() - remove the inspector from the DOM
     * showForElement(element) - add the inspector to the DOM and populate for the given element
     */

    return InspectorAbstract;
});