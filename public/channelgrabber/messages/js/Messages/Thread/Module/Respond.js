define([
    'Messages/Thread/ModuleAbstract'
], function(
    ModuleAbstract
) {
    var Respond = function(message)
    {
        ModuleAbstract.call(this, message);

        var init = function()
        {
console.log('Respond initialised');
        };
        init.call(this);
    };

    Respond.prototype = Object.create(ModuleAbstract.prototype);

    return Respond;
});