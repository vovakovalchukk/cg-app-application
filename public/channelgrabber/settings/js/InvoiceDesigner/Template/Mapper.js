define([
    'require',
    'InvoiceDesigner/Template/Entity',
    'InvoiceDesigner/Template/Element/Box',
    'InvoiceDesigner/Template/Element/DeliveryAddress',
    'InvoiceDesigner/Template/Element/Image',
    'InvoiceDesigner/Template/Element/OrderTable',
    'InvoiceDesigner/Template/PaperPage/Entity',
    'InvoiceDesigner/Template/Element/SellerAddress',
    'InvoiceDesigner/Template/Element/Text',
    'InvoiceDesigner/Template/Element/Mapper/Box',
    'InvoiceDesigner/Template/Element/Mapper/DeliveryAddress',
    'InvoiceDesigner/Template/Element/Mapper/Image',
    'InvoiceDesigner/Template/Element/Mapper/OrderTable',
    'InvoiceDesigner/Template/PaperPage/Mapper',
    'InvoiceDesigner/Template/Element/Mapper/SellerAddress',
    'InvoiceDesigner/Template/Element/Mapper/Text'
], function(require)
{
    var Mapper = function()
    {

    };

    Mapper.PATH_TO_TEMPLATE_ENTITY = 'InvoiceDesigner/Template/Entity';
    Mapper.PATH_TO_ELEMENT_TYPES = 'InvoiceDesigner/Template/Element/';
    Mapper.PATH_TO_ELEMENT_TYPE_MAPPERS = 'InvoiceDesigner/Template/Element/Mapper/';
    Mapper.PATH_TO_PAGE_ENTITY = 'InvoiceDesigner/Template/PaperPage/Entity';
    Mapper.PATH_TO_PAGE_MAPPER = 'InvoiceDesigner/Template/PaperPage/Mapper';

    Mapper.prototype.fromJson = function(json)
    {
        if (typeof json !== 'object') {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\Mapper::fromJson must be passed a JSON object';
        }

        var TemplateClass = require(Mapper.PATH_TO_TEMPLATE_ENTITY);
        var template = new TemplateClass();
        var populating = true;
        template.hydrate(json, populating);
        for (var key in json.elements) {
            var elementData = json.elements[key];
            var element = this.elementFromJson(elementData, populating);
            template.addElement(element, populating);
        }
        var PaperPageClass = require(Mapper.PATH_TO_PAGE_ENTITY);
        var paperPage = new PaperPageClass();
        paperPage.hydrate(json.paperPage, populating);
        template.setPaperPage(paperPage);

        return template;
    };

    Mapper.prototype.elementFromJson = function(elementData, populating)
    {
        var elementType = elementData.type.ucfirst();
        elementData.x = elementData.x.ptToMm().roundToNearest(0.5);
        elementData.y = elementData.y.ptToMm().roundToNearest(0.5);
        elementData.height = elementData.height.ptToMm().roundToNearest(0.5);
        elementData.width = elementData.width.ptToMm().roundToNearest(0.5);
        var elementClass = require(Mapper.PATH_TO_ELEMENT_TYPES + elementType);
        var element = new elementClass();
        if (elementData.padding) {
            elementData.padding = elementData.padding.ptToMm();
        }
        if (elementData.lineHeight) {
            elementData.lineHeight = elementData.lineHeight.ptToMm();
        }
        element.hydrate(elementData, populating);
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
            paperPage: template.getPaperPage().toJson(),
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
        var paperPage = template.getPaperPage();
        var pageMapper = require(Mapper.PATH_TO_PAGE_MAPPER);

        var elementsHtml = '';
        var elements = template.getElements();
        elements.each(function(element) {
            var elementType = element.getType().ucfirst();
            var elementMapper = require(Mapper.PATH_TO_ELEMENT_TYPE_MAPPERS + elementType);
            var elementHtml = elementMapper.toHtml(element);
            elementsHtml += elementHtml;
        });

        paperPage.htmlContents(elementsHtml);
        var html = pageMapper.toHtml(paperPage);

        return html;
    };

    return new Mapper();
});