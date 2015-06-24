define([

], function(

) {
    var ModuleAbstract = function(message)
    {
        this.getMessage = function()
        {
            return message;
        };
    };

    return ModuleAbstract;
});