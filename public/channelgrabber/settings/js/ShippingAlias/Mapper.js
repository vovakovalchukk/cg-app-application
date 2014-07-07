define(function()
{
    var Mapper  = function() { };

    Mapper.prototype.fromCollectionToOptions = function (collection)
    {
        var options = [];

        for (item in collection) {
            var value = collection[item].id;
            var title = collection[item].method;
            options.push({
                title: title,
                value: value
            });
        }
        return options;
    };

    return new Mapper();
});