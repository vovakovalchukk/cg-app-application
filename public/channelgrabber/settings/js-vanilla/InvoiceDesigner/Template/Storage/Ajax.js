define([
        'InvoiceDesigner/Template/StorageAbstract',
        'InvoiceDesigner/Template/Module/ElementResizeMove',
        'jquery',
        'InvoiceDesigner/Template/Element/Helpers/Element',
    ],
    function(
        StorageAbstract,
        ElementResizeMove,
        $,
        ElementHelper
    ) {
        var Ajax = function() {
            StorageAbstract.call(this);
        };

        Ajax.prototype = Object.create(StorageAbstract.prototype);

        Ajax.prototype.fetch = function(id) {
            var template;
            if (!id) {
                throw 'InvalidArgumentException: InvoiceDesigner\Template\Storage\Ajax::fetch must be passed an id';
            }
            $.ajax({
                'url': '/settings/invoice/fetch',
                'data': {'id': id},
                'method': 'POST',
                'dataType': 'json',
                'async': false,
                'success': data => {
                    let templateData = JSON.parse(data['template']);
                    template = this.getMapper().fromJson(templateData);
                },
                'error': function() {
                    throw 'Unable to load template';
                }
            });
            return template;
        };

        Ajax.prototype.save = function(template) {
            const errorMap = {
                "413": "Template is too large to save, try resizing or removing large elements like images"
            };

            n.notice('Preparing template');
            const templateJSON = this.getMapper().toJson(template);
            const templateString = JSON.stringify(templateJSON);

            const invalidElementIds = getInvalidElementIds(template, templateJSON);
            if (invalidElementIds.length) {
                applyBordersToInvalidElements(template, invalidElementIds);
                n.error('Template cannot be saved due to elements outside of the printable area');
                return;
            }

            n.notice('Saving template');

            $.ajax({
                'url': '/settings/invoice/save',
                'data': {'template': templateString},
                'method': 'POST',
                'dataType': 'json',
                'async': true,
                'success': data => {
                    const mappedTemplate = this.getMapper().fromJson(JSON.parse(data['template']));
                    template.setStoredETag(mappedTemplate.getStoredETag());
                    if (!template.getId()) {
                        template.setId(mappedTemplate.getId());
                    }
                    n.success('Template Saved');
                },
                'error': request => {
                    if (request.status in errorMap) {
                        n.error(errorMap[request.status]);
                        return;
                    }
                    n.ajaxError(request);
                }
            });
        };

        return new Ajax();

        function getInvalidElementIds(template, templateJSON) {
            const invalidIds = [];
            const templateElements = template.getElements().getItems();
            for (let elementJSON of templateJSON.elements) {
                let element = templateElements[elementJSON.id]
                let domId = ElementHelper.getElementDomId(element);
                let isElementValid = ElementResizeMove.isElementInPrintableArea(domId);
                if (!isElementValid) {
                    invalidIds.push(elementJSON.id);
                }
            }
            return invalidIds;
        }
        function applyBordersToInvalidElements(template, elementIds) {
            const templateElements = template.getElements().getItems();
            elementIds.forEach((id, index) => {
                let element = templateElements[id];
                let populating = elementIds.length === 1 || index < elementIds.length - 1;
                element.setErrorBorder(true, populating);
            });
        }
    });