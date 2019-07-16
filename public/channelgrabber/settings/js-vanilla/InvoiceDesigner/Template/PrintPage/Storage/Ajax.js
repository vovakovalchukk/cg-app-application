define(['InvoiceDesigner/Template/PaperType/StorageAbstract', 'jquery'], function(StorageAbstract, $)
{
    var Ajax = function()
    {
        StorageAbstract.call(this);
    };

    Ajax.prototype = Object.create(StorageAbstract.prototype);

    Ajax.prototype.fetchAll = function()
    {
        let data = "this is some dummy data"

//        return this.getMapper().fromArray(data);
    };
    return new Ajax();
});