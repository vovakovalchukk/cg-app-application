define([
    'InvoiceDesigner/ModuleAbstract',
    'InvoiceDesigner/Module/DomListener/TemplateSelector',
    'InvoiceDesigner/Template/Service'
], function(
    ModuleAbstract,
    templateSelectorListener,
    templateService
) {
    var TemplateSelector = function()
    {
        ModuleAbstract.call(this);
        var service = templateService;
        this.setDomListener(templateSelectorListener);

        this.getService = function()
        {
            return service;
        };
    };

    TemplateSelector.prototype = Object.create(ModuleAbstract.prototype);

    TemplateSelector.prototype.init = function(application)
    {
        ModuleAbstract.prototype.init.call(this, application);
        this.getDomListener().init(this);

        // TODO remove: debug to get template modules to load
        //this.getService().loadModules();
    };

    TemplateSelector.prototype.selectionMade = function(id)
    {
        /*
         * TODO (CGIV-2002)
         * This will be called by domListener when the user chooses a template
         * Service::fetch(id)
         * Service::loadModules(template)
         */
    };

    return new TemplateSelector();
});