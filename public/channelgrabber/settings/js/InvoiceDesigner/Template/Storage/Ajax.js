define(['InvoiceDesigner/Template/StorageAbstract', 'jquery'], function(StorageAbstract, $)
{
    var Ajax = function()
    {
        StorageAbstract.call(this);
    };

    Ajax.prototype = Object.create(StorageAbstract.prototype);

    Ajax.prototype.fetch = function(id)
    {
        var template;
        var self = this;
        if (!id) {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\Storage\Ajax::fetch must be passed an id';
        }
        $.ajax({
            'url' : '/settings/invoice/fetch',
            'data' : {'id' : id},
            'method' : 'POST',
            'dataType' : 'json',
            'async' : false,
            'success' : function(data) {
                template = self.getMapper().fromJson(JSON.parse(data['template']));
            },
            'error' : function () {
                throw 'Unable to load template';
            }
        });
        return template;
    };

    Ajax.prototype.save = function(template)
    {
        var self = this;
        console.log(self.getMapper().toJson(template));
        $.ajax({
            'url' : '/settings/invoice/save',
            'data' : {'template' : JSON.stringify(self.getMapper().toJson(template))},
            'method' : 'POST',
            'success' : function() {

            },
            'error' : function () {
                throw 'Unable to save template';
            }
        });
    };

    return new Ajax();
});