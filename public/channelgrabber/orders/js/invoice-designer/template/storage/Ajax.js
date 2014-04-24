define(['../StorageAbstract', '../Entity'], function(StorageAbstract, Entity)
{
    var Ajax = function(mapper)
    {
        StorageAbstract.call(this, mapper);
    };

    Ajax.prototype = Object.create(StorageAbstract.prototype);

    Ajax.prototype.fetch = function(id)
    {
        if (!id) {
            throw 'InvalidArgumentException: InvoiceDesigner Storage Ajax::fetch must be passed an id';
        }

        /*
         * TODO (CGIV-2009)
         */
    };

    Ajax.prototype.save = function(template)
    {
        if (!(template instanceof Entity)) {
            throw 'InvalidArgumentException: InvoiceDesigner Storage Ajax::save must be passed an instance of Template Entity';
        }

        /*
         * TODO (CGIV-2009)
         */
    };

    return Ajax;
});