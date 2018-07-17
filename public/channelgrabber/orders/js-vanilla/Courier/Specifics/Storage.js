define(function()
{
    var instance = null;

    function Storage() {}

    Storage.getInstance = function()
    {
        if (instance === null) {
            instance = new Storage();
        }
        return instance;
    };

    return Storage.getInstance();
});