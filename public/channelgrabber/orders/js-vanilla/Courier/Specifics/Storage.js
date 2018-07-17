define(function()
{
    function Storage()
    {
        this.data = {};
    }

    Storage.prototype.set = function(key, value)
    {
        this.data[key] = value;
        return this;
    }

    Storage.prototype.get = function(key)
    {
        return this.data[key];
    }

    return new Storage();
});