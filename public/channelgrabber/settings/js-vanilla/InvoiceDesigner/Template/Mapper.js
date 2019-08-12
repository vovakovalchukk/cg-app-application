define([
    'require',
    'InvoiceDesigner/Template/Element/Mapper/Box',
    'InvoiceDesigner/Template/Element/Mapper/DeliveryAddress',
    'InvoiceDesigner/Template/Element/Mapper/Image',
    'InvoiceDesigner/Template/Element/Mapper/OrderTable',
    'InvoiceDesigner/Template/Element/Mapper/PPI',
    'InvoiceDesigner/Template/Element/Mapper/Label',
    'InvoiceDesigner/Template/Element/Mapper/SellerAddress',
    'InvoiceDesigner/Template/Element/Mapper/Text',
    'InvoiceDesigner/Template/Element/Mapper/Barcode',
    'InvoiceDesigner/Template/Entity',
    'InvoiceDesigner/Template/PaperPage/Entity',
    'InvoiceDesigner/Template/PrintPage/Entity',
    'InvoiceDesigner/Template/MultiPage/Entity',
    'InvoiceDesigner/Template/PaperPage/Mapper'
], function(require)
{
    var Mapper = function()
    {

    };

    Mapper.PATH_TO_TEMPLATE_ENTITY = 'InvoiceDesigner/Template/Entity';
    Mapper.PATH_TO_ELEMENT_TYPE_MAPPERS = 'InvoiceDesigner/Template/Element/Mapper/';
    Mapper.PATH_TO_PAGE_ENTITY = 'InvoiceDesigner/Template/PaperPage/Entity';
    Mapper.PATH_TO_PAGE_MAPPER = 'InvoiceDesigner/Template/PaperPage/Mapper';
    Mapper.PATH_TO_PRINT_PAGE_ENTITY = 'InvoiceDesigner/Template/PrintPage/Entity';
    Mapper.PATH_TO_MULTI_PAGE_ENTITY = 'InvoiceDesigner/Template/MultiPage/Entity';

    Mapper.prototype.createNewTemplate = function()
    {
        const TemplateClass = require(Mapper.PATH_TO_TEMPLATE_ENTITY);
        const template = new TemplateClass();

        const PaperPageClass = require(Mapper.PATH_TO_PAGE_ENTITY);
        const paperPage = new PaperPageClass();
        template.setPaperPage(paperPage);

        const PrintPageClass = require(Mapper.PATH_TO_PRINT_PAGE_ENTITY);
        const printPage = new PrintPageClass();
        template.setPrintPage(printPage);

        const MultiPageClass = require(Mapper.PATH_TO_MULTI_PAGE_ENTITY);
        const multiPage = new MultiPageClass();
        template.setMultiPage(multiPage);

        return template;
    };

    Mapper.prototype.fromJson = function(json)
    {
        if (typeof json !== 'object') {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\Mapper::fromJson must be passed a JSON object';
        }
        var json = JSON.parse(JSON.stringify(json));

        var template = this.createNewTemplate();
        var populating = true;
        template.hydrate(json, populating);
        for (var key in json.elements) {
            var elementData = json.elements[key];
            var element = this.elementFromJson(elementData, populating);
            template.addElement(element, populating);
        }

        let paperPage = template.getPaperPage();
        this.hydratePaperPageFromJson(paperPage, json.paperPage, populating);
        template.setPaperPage(paperPage).setEditable(!!json.editable);

        let printPage = template.getPrintPage();
        this.hydratePrintPageFromJson(printPage, json.printPage, populating);
        template.setPrintPage(printPage).setEditable(!!json.editable);

        return template;
    };

    Mapper.prototype.createNewElement = function(elementType)
    {
        var elementMapper = require(Mapper.PATH_TO_ELEMENT_TYPE_MAPPERS + elementType);
        return elementMapper.createElement();
    };

    Mapper.prototype.elementFromJson = function(elementData, populating)
    {
        var elementType = elementData.type.ucfirst();
        elementData.x = Number(elementData.x).ptToMm();
        elementData.y = Number(elementData.y).ptToMm();
        elementData.height = Number(elementData.height).ptToMm();
        elementData.width = Number(elementData.width).ptToMm();
        var element = this.createNewElement(elementType);
        if (elementData.padding) {
            elementData.padding = Number(elementData.padding).ptToMm();
        }
        if (elementData.lineHeight) {
            elementData.lineHeight = Number(elementData.lineHeight).ptToMm();
        }
        if (elementData.borderWidth) {
            elementData.borderWidth = Number(elementData.borderWidth).ptToMm(); 
        }
        element.hydrate(elementData, populating);
        return element;
    };

    Mapper.prototype.hydratePaperPageFromJson = function(paperPage, json, populating)
    {
        json.width = Number(json.width).ptToMm();
        json.height = Number(json.height).ptToMm();

        paperPage.hydrate(json, populating);
    };

    Mapper.prototype.hydratePrintPageFromJson = function(printPage, json, populating)
    {
        printPage.hydrate(json, populating);
    };

    Mapper.prototype.toJson = function(template)
    {
        var json = {
            storedETag: template.getStoredETag(),
            id: template.getId(),
            type: template.getType(),
            typeId: template.getTypeId(),
            name: template.getName(),
            organisationUnitId: template.getOrganisationUnitId(),
            paperPage: template.getPaperPage().toJson(),
            printPage: template.getPrintPage().toJson(),
            elements: [],
            editable: template.isEditable()
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
        let printPage = template.getPrintPage();
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