define([
    'require',
    'InvoiceDesigner/Template/Storage/Ajax',
    'InvoiceDesigner/Template/Mapper',
    'InvoiceDesigner/Template/DomManipulator',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    // Template Module requires here
    'InvoiceDesigner/Template/Module/PaperType',
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
            throw 'InvalidArgumentException: InvoiceDesigner\Template\Service::fetch must be passed a template ID';
        }
        var template = this.getStorage().fetch(id);
        template.setState(Service.FETCHED_STATE)
            .setStateId(id);
        this.getDomManipulator().hideSaveDiscardBar(template);
        return template;
    };

    Service.prototype.save = function(template)
    {
        if (! template.isEditable()) {
            template = this.duplicate(template);
        }
        this.getStorage().save(template);
        template.setState(Service.FETCHED_STATE)
            .setStateId(template.getId());
        return this;
    };

    Service.prototype.createForOu = function(organisationUnitId)
    {
        var template = this.getMapper().createNewTemplate();
        template.setOrganisationUnitId(organisationUnitId)
            .setState(Service.CREATED_STATE)
            .setStateId(organisationUnitId);
        this.loadModules(template);
        this.getDomManipulator().hideSaveDiscardBar(template);
        return template;
    };

    Service.prototype.duplicate = function(template)
    {
        template.setName('DUPLICATE - ' + template.getName())
            .setState(Service.DUPLICATED_STATE)
            .setStateId(template.getId())
            .setId()
            .setEditable(true);
        this.loadModules(template);
        this.getDomManipulator().hideSaveDiscardBar(template);
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
        var html = this.getMapper().toHtml(template);
        this.getDomManipulator().insertTemplateHtml(html);
        return this;
    };

    return new Service();
});
