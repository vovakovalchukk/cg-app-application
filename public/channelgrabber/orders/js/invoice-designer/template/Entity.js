define(['./element/Collection'], function(ElementCollection)
{
    var Entity = function()
    {
        var elements = new ElementCollection();

        this.getElements = function()
        {
            return elements;
        };

        /*
         * TODO (CGIV-2009)
         */
    };

    return Entity;
});