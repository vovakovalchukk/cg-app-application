define(['./DomListener/TemplateSelector'], function(domListener)
{
    var TemplateSelector = function()
    {
        var domListener = domListener;
        var application;

        this.getApplication = function()
        {
            return application;
        };

        this.setApplication = function(newApplication)
        {
            application = newApplication;
            return this;
        };

        this.getDomListener = function()
        {
            return domListener;
        };
    };

    TemplateSelector.protoype.init = function(application)
    {
        this.setApplication(application);
        this.getDomListener().init(this);
    };

    TemplateSelector.prototype.selectionMade = function(id)
    {
        /*
         * TODO (CGIV-2009)
         * This will be called by domListener when the user chooses a template
         * Service::fetch(id)
         * Service::loadModules(template)
         */
    };

    /*
     * TODO (CGIV-2009)
     */

    return new TemplateSelector();
});