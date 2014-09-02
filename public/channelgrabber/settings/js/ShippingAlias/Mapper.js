define(function()
{
    var Mapper  = function() { };

    Mapper.prototype.fromCollectionToOptions = function (collection)
    {
        var options = [];

        for (item in collection) {
            var value = collection[item].id;
            if(collection[item].hasOwnProperty('method')) {
                var title = collection[item].method;
            } else if(collection[item].hasOwnProperty('displayName')) {
                var title = collection[item].displayName
            }

            options.push({
                title: title,
                value: value
            });
        }
        return options;
    };

    return new Mapper();
});