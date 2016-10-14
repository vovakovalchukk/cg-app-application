define(['InvoiceDesigner/CollectionAbstract'], function(CollectionAbstract)
{
    var Collection = function()
    {
        CollectionAbstract.call(this);
    };

    Collection.prototype = Object.create(CollectionAbstract.prototype);

    return Collection;
});