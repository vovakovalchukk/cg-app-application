define(function()
{
    var CollectionAbstract = function()
    {
        var items = {};

        this.getItems = function()
        {
            return items;
        };
    };

    CollectionAbstract.prototype.attach = function(item)
    {
        if (!item.hasMethod('getId')) {
            throw 'InvalidArgumentException: InvoiceDesigner\CollectionAbstract::attach() must be passed'+
                ' a valid item object';
        }
        this.getItems()[item.getId()] = item;
        return this;
    };

    CollectionAbstract.prototype.detach = function(item)
    {
        if (!item.hasMethod('getId')) {
            throw 'InvalidArgumentException: InvoiceDesigner\CollectionAbstract::attach() must be passed'+
                ' a valid item object';
        }
        delete this.getItems()[item.getId()];
        return this;
    };

    CollectionAbstract.prototype.count = function()
    {
        var items = this.getItems();
        var count = 0;
        for (var id in items) {
            if (items.hasOwnProperty(id)) {
                count++;
            }
        }
        return count;
    };

    CollectionAbstract.prototype.each = function(callback)
    {
        if (typeof callback !== 'function') {
            throw 'InvalidArgumentException: InvoiceDesigner\CollectionAbstract::each() must be passed'+
                ' a valid callback function';
        }
        var items = this.getItems();
        for (var id in items) {
            callback(items[id]);
        }
        return this;
    };

    CollectionAbstract.prototype.containsId = function(id)
    {
        return (this.getItems()[id] !== undefined);
    };

    CollectionAbstract.prototype.merge = function(collection)
    {
        var self = this;
        collection.each(function(item)
        {
            self.attach(item);
        });
        return this;
    };

    return CollectionAbstract;
});