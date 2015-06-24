define([
    'Messages/ModuleAbstract'
], function(
    ModuleAbstract
) {
    var MessageBody = function(application) 
    {
        ModuleAbstract.call(this, application);

        var init = function()
        {
console.log('MessageBody initialised');
        };
        init.call(this);
    };

    MessageBody.prototype = Object.create(ModuleAbstract.prototype);

    return MessageBody;
});