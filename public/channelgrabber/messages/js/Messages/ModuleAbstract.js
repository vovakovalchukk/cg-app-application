define([

], function(

) {
    var ModuleAbstract = function(application)
    {
        this.getApplication = function()
        {
            return application;
        };
    };

    return ModuleAbstract;
});