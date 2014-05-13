define([
    'require',
    'InvoiceDesigner/Template/Entity',
    'InvoiceDesigner/Template/Element/Box',
    'InvoiceDesigner/Template/Element/DeliveryAddress',
    'InvoiceDesigner/Template/Element/Image',
    'InvoiceDesigner/Template/Element/OrderTable',
    'InvoiceDesigner/Template/Element/Paper',
    'InvoiceDesigner/Template/Element/SellerAddress',
    'InvoiceDesigner/Template/Element/Text'
], function(require)
{
    var Mapper = function()
    {

    };

    Mapper.prototype.fromJson = function(json)
    {
        if (typeof json !== 'object') {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\Mapper::fromJson must be passed a JSON object';
        }

        var template = require('InvoiceDesigner/Template/Entity');
        var populating = true;
        template.hydrate(json, populating);
        for (var key in json.elements) {
            var elementData = json.elements[key];
            var element = this.elementFromJson(elementData);
            template.addElement(element);
        }

        return template;
    };

    Mapper.prototype.elementFromJson = function(elementData)
    {
        var elementType = elementData.templateType.charAt(0).toUpperCase() + elementData.templateType.substr(1);
        var element = require('InvoiceDesigner/Template/Element/' + elementType);
        for (var field in elementData) {
            var setter = 'set' + field.charAt(0).toUpperCase() + field.substr(1);
            if (element[setter]) {
                element[setter](elementData[field]);
            }
        }
        return element;
    };

    Mapper.prototype.toJson = function(template)
    {
        var json = {
            id: template.getId(),
            type: template.getType(),
            name: template.getName(),
            organisationUnitId: template.getOrganisationUnitId(),
            minHeight: template.getMinHeight(),
            minWidth: template.getMinWidth(),
            elements: []
        };

        template.getElements().each(function(element)
        {
            json.elements.push(element.toJson());
        });

        return json;
    };

    Mapper.prototype.toHtml = function(template)
    {
        /*
         * TODO (CGIV-2026)
         */
    };

    return new Mapper();
});