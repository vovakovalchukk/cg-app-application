define([
    'require',
    'InvoiceDesigner/Template/Entity',
    'InvoiceDesigner/Template/Element/Box',
    'InvoiceDesigner/Template/Element/DeliveryAddress',
    'InvoiceDesigner/Template/Element/Image',
    'InvoiceDesigner/Template/Element/OrderTable',
    'InvoiceDesigner/Template/Element/Page',
    'InvoiceDesigner/Template/Element/SellerAddress',
    'InvoiceDesigner/Template/Element/Text',
    'InvoiceDesigner/Template/Element/Mapper/Box',
//    'InvoiceDesigner/Template/Element/Mapper/DeliveryAddress',
//    'InvoiceDesigner/Template/Element/Mapper/Image',
//    'InvoiceDesigner/Template/Element/Mapper/OrderTable',
    'InvoiceDesigner/Template/Element/Mapper/Page'
//,    'InvoiceDesigner/Template/Element/Mapper/SellerAddress',
//    'InvoiceDesigner/Template/Element/Mapper/Text'
], function(require)
{
    var Mapper = function()
    {

    };

    Mapper.PATH_TO_ELEMENT_ENTITY = 'InvoiceDesigner/Template/Entity';
    Mapper.PATH_TO_ELEMENT_TYPES = 'InvoiceDesigner/Template/Element/';
    Mapper.PATH_TO_ELEMENT_TYPE_MAPPERS = 'InvoiceDesigner/Template/Element/Mapper/';
    Mapper.PATH_TO_PAGE_MAPPER = 'InvoiceDesigner/Template/Element/Mapper/Page';

    Mapper.prototype.fromJson = function(json)
    {
        if (typeof json !== 'object') {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\Mapper::fromJson must be passed a JSON object';
        }

        var template = require(Mapper.PATH_TO_ELEMENT_ENTITY);
        var populating = true;
        template.hydrate(json, populating);

        for (var key in json.elements) {
            var elementData = json.elements[key];
            var element = this.elementFromJson(elementData);
            template.addElement(element, populating);
        }

        return template;
    };

    Mapper.prototype.elementFromJson = function(elementData)
    {
        var elementType = elementData.type.charAt(0).toUpperCase() + elementData.type.substr(1);
        var element = require(Mapper.PATH_TO_ELEMENT_TYPES + elementType);
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
        var page = template.getPage();
        var pageMapper = require(Mapper.PATH_TO_PAGE_MAPPER);

        var elementsHtml = '';
        var elements = template.getElements();
        elements.each(function(element) {
            if (element.getId() === page.getId()) {
                return true;
            }
            var elementType = element.getType().charAt(0).toUpperCase() + element.getType().substr(1);
            var elementMapper = require(Mapper.PATH_TO_ELEMENT_TYPE_MAPPERS + elementType);
            var elementHtml = elementMapper.toHtml(element);
            elementsHtml += elementHtml;
        });

        page.setContent(elementsHtml);
        var html = pageMapper.toHtml(page);

        return html;
    };

    return new Mapper();
});