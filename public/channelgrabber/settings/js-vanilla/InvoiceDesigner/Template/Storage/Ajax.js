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
            'success' : function invoiceFetchSuccess(data) {
                let templateData = JSON.parse(data['template']);

                //todo - remove this hack
                templateData.multiPage = {
                    rows: 2,
                    columns: 4,
                    width: 200,
                    height: 150
                };

                template = self.getMapper().fromJson(templateData);
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

        var errorMap = {
            "413": "Template is too large to save, try resizing or removing large elements like images"
        };
        
        n.notice('Preparing template');

        var templateJSON = self.getMapper().toJson(template);
        var templateString = JSON.stringify(templateJSON);
        n.notice('Saving template');

        $.ajax({
            'url' : '/settings/invoice/save',
            'data' : {'template' : templateString},
            'method' : 'POST',
            'dataType' : 'json',
            'async' : true,
            'success' : function(data) {
                var mappedTemplate = self.getMapper().fromJson(JSON.parse(data['template']));
                template.setStoredETag(mappedTemplate.getStoredETag());
                if (!template.getId()) {
                    template.setId(mappedTemplate.getId());
                }
                n.success('Template Saved');
            },
            'error' : function (request) {
                if (request.status in errorMap) {
                    n.error(errorMap[request.status]);
                    return;
                }
                n.ajaxError(request);
            }
        });
    };

    return new Ajax();
});