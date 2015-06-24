define([
    'Messages/ModuleAbstract'
], function(
    ModuleAbstract
) {
    var ThreadDetails = function(application)
    {
        ModuleAbstract.call(this, application);

        var init = function()
        {
console.log('ThreadDetails initialised');
        };
        init.call(this);
    };

    ThreadDetails.prototype = Object.create(ModuleAbstract.prototype);

    return ThreadDetails;
});