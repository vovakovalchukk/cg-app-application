define([
    'require',
    'InvoiceDesigner/Template/Storage/Ajax',
    'InvoiceDesigner/Template/Mapper',
    'InvoiceDesigner/Template/DomManipulator',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    // Template Module requires here
    'InvoiceDesigner/Template/Module/PaperType',
    'InvoiceDesigner/Template/Module/TemplateType',
    'InvoiceDesigner/Template/Module/PrintPage',
    'InvoiceDesigner/Template/Module/InspectorManager',
    'InvoiceDesigner/Template/Module/Renderer',
    'InvoiceDesigner/Template/Module/ElementManager',
    'InvoiceDesigner/Template/Module/AddDiscardBar',
    'InvoiceDesigner/Template/Module/Name',
    'InvoiceDesigner/Template/Module/ImageUpload',
    'InvoiceDesigner/Template/Module/ElementResizeMove',
    'InvoiceDesigner/Template/Module/ToPdf'
], function(
    require,
    templateAjaxStorage,
    templateMapper,
    templateDomManipulator,
    ElementMapperAbstract
) {
    var Service = function()
    {
        var storage = templateAjaxStorage;
        var mapper = templateMapper;
        var domManipulator = templateDomManipulator;

        var modules = [
            // Template Modules require() paths here
            'InvoiceDesigner/Template/Module/PaperType',
            'InvoiceDesigner/Template/Module/TemplateType',
            'InvoiceDesigner/Template/Module/PrintPage',
            'InvoiceDesigner/Template/Module/InspectorManager',
            'InvoiceDesigner/Template/Module/Renderer',
            'InvoiceDesigner/Template/Module/ElementManager',
            'InvoiceDesigner/Template/Module/AddDiscardBar',
            'InvoiceDesigner/Template/Module/Name',
            'InvoiceDesigner/Template/Module/ImageUpload',
            'InvoiceDesigner/Template/Module/ElementResizeMove',
            'InvoiceDesigner/Template/Module/ToPdf'
        ];

        this.getStorage = function()
        {
            return storage;
        };

        this.setStorage = function(newStorage)
        {
            storage = newStorage;
            return this;
        };

        this.getMapper = function()
        {
            return mapper;
        };

        this.setMapper = function(newMapper)
        {
            mapper = newMapper;
            return this;
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
        };

        this.setDomManipulator = function(newDomManipulator)
        {
            domManipulator = newDomManipulator;
            return this;
        };

        this.getModules = function()
        {
            return modules;
        };
    };

    Service.FETCHED_STATE = 'fetchAndLoadModules';
    Service.DUPLICATED_STATE = 'fetchAndDuplicate';
    Service.CREATED_STATE = 'createForOu';

    Service.prototype.fetch = function(id)
    {
        if (!id) {
            throw 'InvalidArgumentException: InvoiceDesigner\\Template\\Service::fetch must be passed a template ID';
        }
        var template = this.getStorage().fetch(id);

        template.setState(Service.FETCHED_STATE)
            .setStateId(id);
        this.getDomManipulator().hideSaveDiscardBar(template)
            .triggerTemplateSelectedEvent(template);
        return template;
    };

    Service.prototype.save = function(template)
    {
        if (! this.validateTemplate(template)) {
            return false;
        }
        
        try {
            this.getStorage().save(template);
            template.setState(Service.FETCHED_STATE)
                .setStateId(template.getId());
            return true;
        } catch(e){
            n.error(e);
            return false;
        }
    };

    Service.prototype.validateTemplate = function(template)
    {
        if(!template.getName()) {
            n.error("Please enter a template name.");
            return false;
        }
        if(!template.getElements().count()){
            n.error("Please add an element to the template.");
            return false;
        }
        return true;
    };

    Service.prototype.createForOu = function(organisationUnitId)
    {
        var template = this.getMapper().createNewTemplate();
        template.setOrganisationUnitId(organisationUnitId)
            .setState(Service.CREATED_STATE)
            .setStateId(organisationUnitId);
        this.loadModules(template);
        this.getDomManipulator().hideSaveDiscardBar(template)
            .triggerTemplateSelectedEvent(template);

        return template;
    };

    Service.prototype.duplicate = function(template)
    {
        template.setName('DUPLICATE - ' + template.getName())
            .setState(Service.DUPLICATED_STATE)
            .setId()
            .setEditable(true);
        if (template.getId()) {
            template.setStateId(template.getId());
        }
        this.loadModules(template);
        return template;
    };

    Service.prototype.fetchAndDuplicate = function(id)
    {
        var template = this.fetch(id);
        return this.duplicate(template);
    };

    Service.prototype.showAsPdf = function(template)
    {
        var form = $('form.toPdfButton');
        form.find('input').val(JSON.stringify(template));
        form.submit();
    };

    Service.prototype.loadModules = function(template)
    {
        var modules = this.getModules();
        for (var key in modules) {
            var module = require(modules[key]);
            module.init(template, this);
        }
    };

    Service.prototype.fetchAndLoadModules = function(id)
    {
        var template = this.fetch(id);
        this.loadModules(template);
        return template;
    };

    Service.prototype.render = function(template)
    {
        const paperPage = template.getPaperPage();
        const printPage = template.getPrintPage();

        const templatePageElementId = ElementMapperAbstract.getDomId(paperPage);

        let html = this.getMapper().toHtml(template);
        let domManipulator = this.getDomManipulator();
        domManipulator.insertTemplateHtml(html);
        const templatePageElement = document.getElementById(templatePageElementId);

        printPage.render(template, templatePageElement);

        return this;
    };

    return new Service();
});
