define(function()
{
    var AccountCollection = function()
    {
        var items = [];

        this.getItems = function()
        {
            return items;
        };

        this.setItems = function(newItems)
        {
            items = newItems;
            return this;
        };
    };
    return new AccountCollection();
});