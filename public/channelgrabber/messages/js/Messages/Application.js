define([
    // Application Module requires here
    'Messages/Module/Filters',
    'Messages/Module/ThreadList',
    'Messages/Module/ThreadDetails'
], function(
    // Application Module variables here
    Filters,
    ThreadList,
    ThreadDetails
) {
    var Application = function(organisationUnitId, userId)
    {
        var modulesClasses = [
            // Modules here
            Filters,
            ThreadList,
            ThreadDetails
        ];
        var modules = [];

        this.getModules = function()
        {
            return modules;
        };

        this.getOrganisationUnitId = function()
        {
            return organisationUnitId;
        };

        this.getUserId = function()
        {
            return userId;
        };

        var init = function()
        {
            for (var key in modulesClasses) {
                var module = new modulesClasses[key](this);
                modules.push(module);
            }
console.log('Application initialised');
        };
        init.call(this);
    };

    return Application;
});