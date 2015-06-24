define([
    'Messages/ModuleAbstract'
], function(
    ModuleAbstract
) {
    var ThreadList = function(application)
    {
        ModuleAbstract.call(this, application);

        var init = function()
        {
console.log('ThreadList initialised');
        };
        init.call(this);
    };

    ThreadList.prototype = Object.create(ModuleAbstract.prototype);

    return ThreadList;
});