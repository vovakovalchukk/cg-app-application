define([
    './DomListener/TemplateSelector',
    '../Template/Service'
], function(
    templateSelectorListener,
    templateService
) {
    var TemplateSelector = function()
    {
        var domListener = templateSelectorListener;
        var service = templateService;
        var application;

        this.getDomListener = function()
        {
            return domListener;
        };

        this.getService = function()
        {
            return service;
        };

        this.getApplication = function()
        {
            return application;
        };

        this.setApplication = function(newApplication)
        {
            application = newApplication;
            return this;
        };
    };

    TemplateSelector.prototype.init = function(application)
    {
        this.setApplication(application);
        this.getDomListener().init(this);
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