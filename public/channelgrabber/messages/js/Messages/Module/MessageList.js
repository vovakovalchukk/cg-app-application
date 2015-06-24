define([
    'Messages/ModuleAbstract'
], function(
    ModuleAbstract
) {
    var MessageList = function(application)
    {
        ModuleAbstract.call(this, application);

        var init = function()
        {
console.log('MessageList initialised');
        };
        init.call(this);
    };

    MessageList.prototype = Object.create(ModuleAbstract.prototype);

    return MessageList;
});