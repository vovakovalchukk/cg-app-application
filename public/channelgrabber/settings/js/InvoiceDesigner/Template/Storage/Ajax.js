define(['InvoiceDesigner/Template/StorageAbstract', 'jQuery'], function(StorageAbstract, $)
{
    var Ajax = function()
    {
        StorageAbstract.call(this);
    };

    Ajax.prototype = Object.create(StorageAbstract.prototype);

    Ajax.prototype.fetch = function(id)
    {
        if (!id) {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\Storage\Ajax::fetch must be passed an id';
        }

        /*
         * TODO (CGIV-2002)
         */
    };

    Ajax.prototype.save = function(template)
    {
        /*
         * TODO (CGIV-2016)
         */
    };

    return new Ajax();
});