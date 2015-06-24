define([
    // Application Module requires here
    'Messages/Module/Filters',
    'Messages/Module/MessageList',
    'Messages/Module/MessageBody'
], function(
    // Application Module variables here
    Filters,
    MessageList,
    MessageBody
) {
    var Application = function(organisationUnitId, userId)
    {
        var modulesClasses = [
            // Modules here
            Filters,
            MessageList,
            MessageBody
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