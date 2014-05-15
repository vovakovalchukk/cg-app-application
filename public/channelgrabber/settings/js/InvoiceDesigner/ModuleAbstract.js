define(function()
{
    var ModuleAbstract = function()
    {
        var application;
        var domListener;

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

        this.setDomListener = function(newDomListener)
        {
            domListener = newDomListener;
            return this;
        };
    };

    ModuleAbstract.prototype.init = function(application)
    {
        this.setApplication(application);
        this.getDomListener().init(this);
        // Sub-classes should override with a .call() to this method then do their own work
    };

    return ModuleAbstract;
});