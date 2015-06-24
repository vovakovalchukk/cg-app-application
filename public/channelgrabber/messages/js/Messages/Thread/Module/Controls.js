define([
    'Messages/Thread/ModuleAbstract'
], function(
    ModuleAbstract
) {
    var Controls = function(message)
    {
        ModuleAbstract.call(this, message);

        var init = function()
        {
console.log('Controls initialised');
        };
        init.call(this);
    };

    Controls.prototype = Object.create(ModuleAbstract.prototype);

    return Controls;
});