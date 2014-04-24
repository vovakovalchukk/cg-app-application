define(['../StorageAbstract'], function(StorageAbstract)
{
    var Ajax = function()
    {
        StorageAbstract.call(this);
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
        /*
         * TODO (CGIV-2009)
         */
    };

    return new Ajax();
});