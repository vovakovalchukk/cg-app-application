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

    ThreadList.prototype.loadForFilter = function(filter)
    {
        var threads = this.getService().fetchCollectionByFilter(filter);
        this.renderThreads(threads);
    };

    ThreadList.prototype.renderThreads = function(threads)
    {
        
    };

    return ThreadList;
});