define(function()
{
    function Storage() {}

    Storage.prototype.set(key, value)
    {
        this.key = value;
    }

    Storage.prototype.get(key)
    {
        return this.key;
    }

    return new Storage();
});