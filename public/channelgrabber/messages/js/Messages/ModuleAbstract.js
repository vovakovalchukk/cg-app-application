define([
    'Messages/Thread/Service'
], function(
    service
) {
    var ModuleAbstract = function(application)
    {
        this.getApplication = function()
        {
            return application;
        };

        this.getService = function()
        {
            return service;
        };
    };

    return ModuleAbstract;
});