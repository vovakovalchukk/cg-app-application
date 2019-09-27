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

                //todo - remove these hacks
                const tableColumns = [
                    {
                        "id": "quantityOrdered",
                        default: true,
                        "cellPlaceholder": "2",
                        displayText: "QTY",
                        "optionText": "Quantity Ordered",
                        width: 50,
                        widthMeasurementUnit: 'mm'
                    },
                    {
                        "id": "skuOrdered",
                        default: true,
                        displayText: 'Item #',
                        "optionText": "Sku Ordered",
                        "cellPlaceholder": "BATTERY10pc"
                    },
                    {
                        "id": "unitPriceIncVAT",
                        default: true,
                        "displayText": "Price",
                        "optionText": "Unit Price inc VAT",
                        "cellPlaceholder": "£6"
                    }
                ];
                templateData.elements[0].tableColumns = tableColumns;
                templateData.elements[0].tableCells = [];
                tableColumns.map(column => {
                    templateData.elements[0].tableCells.push({
                            column: column.id,
                            cellTag: 'th',
                            bold: true,
//                            fontSize: 22,
                            backgroundColour: '#952f2f',
//                            fontColour: '#952f2f',
//                            align: 'right',
//                            fontFamily: 'Courier'
                    });
                    templateData.elements[0].tableCells.push({
                            column: column.id,
                            cellTag: 'td',
                            bold: true,
                            fontSize: 10,
                            backgroundColor: '',
                            fontColour: '#222',
                            align: 'left',
                            fontFamily: 'Arial'
                    });
                });

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