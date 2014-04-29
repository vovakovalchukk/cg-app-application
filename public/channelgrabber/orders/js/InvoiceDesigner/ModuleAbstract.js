define(function()
{
    var ModuleAbstract = function()
    {
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
    };

    ModuleAbstract.prototype.init = function(application)
    {
        this.setApplication(application);
        // Sub-classes should override with a .call() to this method then do their own work
    };

    return ModuleAbstract;
});