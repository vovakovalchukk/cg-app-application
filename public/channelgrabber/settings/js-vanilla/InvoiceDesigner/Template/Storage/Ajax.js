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
                let jsonTemplate = JSON.parse(data['template']);

                //TODO - remove these hacks before PR
                jsonTemplate.printPage = {
                  margin: {
                      top: 5,
                      bottom: 10,
                      left: 15,
                      right: 20,
                  }
                };
                jsonTemplate.paperPage.measurementUnit = "mm";

                template = self.getMapper().fromJson(jsonTemplate);
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