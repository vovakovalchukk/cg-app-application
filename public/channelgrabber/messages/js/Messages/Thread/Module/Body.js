define([
    'Messages/Thread/ModuleAbstract'
], function(
    ModuleAbstract
) {
    var Body = function(message)
    {
        ModuleAbstract.call(this, message);

        var init = function()
        {
console.log('Body initialised');
        };
        init.call(this);
    };

    Body.prototype = Object.create(ModuleAbstract.prototype);

    return Body;
});