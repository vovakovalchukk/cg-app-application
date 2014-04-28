define(['./Element/Collection'], function(collection)
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

        this.setId = function(newId)
        {
            id = newId;
            return this;
        };

        this.getName = function()
        {
            return name;
        };

        this.setName = function(newName)
        {
            name = newName;
            return this;
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