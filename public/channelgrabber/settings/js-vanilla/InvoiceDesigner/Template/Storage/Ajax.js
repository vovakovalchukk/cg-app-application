define([
        'InvoiceDesigner/Template/StorageAbstract',
        'InvoiceDesigner/Template/Module/ElementResizeMove',
        'InvoiceDesigner/Template/Element/MapperAbstract',
        'jquery'
    ],
    function(
        StorageAbstract,
        ElementResizeMove,
        ElementMapperAbstract,
        $
    ) {
        var Ajax = function() {
            StorageAbstract.call(this);
        };

        Ajax.prototype = Object.create(StorageAbstract.prototype);

        Ajax.prototype.fetch = function(id) {
            var template;
            var self = this;
            if (!id) {
                throw 'InvalidArgumentException: InvoiceDesigner\Template\Storage\Ajax::fetch must be passed an id';
            }
            $.ajax({
                'url': '/settings/invoice/fetch',
                'data': {'id': id},
                'method': 'POST',
                'dataType': 'json',
                'async': false,
                'success': function invoiceFetchSuccess(data) {
                    let templateData = JSON.parse(data['template']);
                    templateData.printPage = {
                        "margin": {
                            "top": 20,
                            "bottom": 20,
                            "left": 20,
                            "right": 20
                        }
                    };
                    template = self.getMapper().fromJson(templateData);
                },
                'error': function() {
                    throw 'Unable to load template';
                }
            });
            return template;
        };

        function getInvalidElementIds(template, templateJSON) {
            const invalidIds = [];
            const domIdPrefix = ElementMapperAbstract.getDomIdPrefix();

            for (let element of templateJSON.elements) {
                let domId = `${domIdPrefix}${element.id}`;
                let isElementValid = ElementResizeMove.isElementInPrintableArea(domId);
                if (!isElementValid) {
                    invalidIds.push(element.id);
                }
            }
            return invalidIds;
        }
        function applyBordersToOffendingElements(template, elementIds) {
            let templateElements = template.getElements().getItems();
            elementIds.forEach((id, index) => {
                let element = templateElements[id];
                let populating = index < elementIds.length - 1;
                element.setErrorBorder(true, populating);
            });
        }

        Ajax.prototype.save = function(template) {
            var self = this;

            var errorMap = {
                "413": "Template is too large to save, try resizing or removing large elements like images"
            };

            n.notice('Preparing template');

            var templateJSON = self.getMapper().toJson(template);
            var templateString = JSON.stringify(templateJSON);

            let invalidElementIds = getInvalidElementIds(template, templateJSON);
            if (invalidElementIds.length) {
                applyBordersToOffendingElements(template, invalidElementIds);
                n.error('Template cannot be saved due to there being some elements outside of the printable area');
                return;
            }

            n.notice('Saving template');

            $.ajax({
                'url': '/settings/invoice/save',
                'data': {'template': templateString},
                'method': 'POST',
                'dataType': 'json',
                'async': true,
                'success': function(data) {
                    var mappedTemplate = self.getMapper().fromJson(JSON.parse(data['template']));
                    template.setStoredETag(mappedTemplate.getStoredETag());
                    if (!template.getId()) {
                        template.setId(mappedTemplate.getId());
                    }
                    n.success('Template Saved');
                },
                'error': function(request) {
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