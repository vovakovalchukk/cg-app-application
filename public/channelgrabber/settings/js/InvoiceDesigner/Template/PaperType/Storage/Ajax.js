define(['InvoiceDesigner/Template/PaperType/StorageAbstract', 'jQuery'], function(StorageAbstract, $)
{
    var Ajax = function()
    {
        StorageAbstract.call(this);
    };

    Ajax.prototype = Object.create(StorageAbstract.prototype);

    Ajax.prototype.fetchAll = function()
    {
        return [];
    };

    return new Ajax();
});