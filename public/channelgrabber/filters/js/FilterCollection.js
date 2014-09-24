define([], function() {

    var FilterCollection = function()
    {
        var storage = {};

        this.getStorage = function()
        {
            return storage;
        };

        this.setStorage = function(newStorage)
        {
            storage = newStorage;
        };
    };

    FilterCollection.prototype.attach = function(name)
    {
        this.getStorage()[name] = name;
    };

    FilterCollection.prototype.detach = function(name)
    {
        delete this.getStorage()[name];
    };

    FilterCollection.prototype.getFilters = function()
    {
        return this.getStorage();
    };

    FilterCollection.prototype.setFilters = function(filters)
    {
        this.setStorage(filters);
    };

    return new FilterCollection();
});