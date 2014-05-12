define(['InvoiceDesigner/Template/StorageAbstract', 'jquery'], function(StorageAbstract, $)
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
        var template;
        $.ajax({
            'url' : 'settings/invoice/fetch',
            'data' : {'id' : id},
            'method' : 'POST',
            'success' : function(data) {
                template = this.getMapper().fromJson(data);
            },
            'error' : function () {
                throw 'Unable to load template';
            }
        });
        return template;
    };

    Ajax.prototype.save = function(template)
    {
        /*
         * TODO (CGIV-2016)
         */
    };

    return new Ajax();
});