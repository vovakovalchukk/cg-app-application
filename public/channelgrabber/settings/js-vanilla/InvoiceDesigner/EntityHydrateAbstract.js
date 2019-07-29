define(function()
{
    var EntityHydrateAbstract = function()
    {
        this.set = function(field, value, populating)
        {
            throw 'RuntimeException: InvoiceDesigner\\EntityHydrateAbstract::set() should be overridden by sub-class';
        };
    };

    EntityHydrateAbstract.prototype.hydrate = function(data, populating)
    {

        for (var field in data)
        {
            if (!this.shouldFieldBeHydrated(field)) {
                continue;
            }
            this.set(field, data[field], populating);
        }
    };

    /**
     * Sub-classes can override this to block certain fields from hydrating
     */
    EntityHydrateAbstract.prototype.shouldFieldBeHydrated = function(field)
    {
        return true;
    };

    return EntityHydrateAbstract;
});