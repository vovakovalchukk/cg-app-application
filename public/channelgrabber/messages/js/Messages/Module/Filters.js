define([
    'Messages/ModuleAbstract'
], function(
    ModuleAbstract
) {
    var Filters = function(application)
    {
        ModuleAbstract.call(this, application);

        var init = function()
        {
console.log('Filters initialised');
        };
        init.call(this);
    };

    Filters.prototype = Object.create(ModuleAbstract.prototype);

    return Filters;
});