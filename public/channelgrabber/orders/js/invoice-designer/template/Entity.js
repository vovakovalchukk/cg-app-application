define(['./element/Collection'], function(collection)
{
    var Entity = function()
    {
        var elements = collection;

        var id;
        var name;

        this.getElements = function()
        {
            return elements;
        };

        this.getId = function()
        {
            return id;
        };

        this.getName = function()
        {
            return name;
        };
    };

    Entity.prototype.addElement = function(element)
    {
        /*
         * TODO (CGIV-2009)
         */
    };

    Entity.prototype.removeElement = function(element)
    {
        /*
         * TODO (CGIV-2009)
         */
    };

    return new Entity();
});