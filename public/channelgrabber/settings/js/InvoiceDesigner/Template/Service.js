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
    'InvoiceDesigner/Template/Module/AddDiscardBar',
    'InvoiceDesigner/Template/Module/Name',
    'InvoiceDesigner/Template/Module/ImageUpload',
    'InvoiceDesigner/Template/Module/ElementResizeMove'
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
            'InvoiceDesigner/Template/Module/AddDiscardBar',
            'InvoiceDesigner/Template/Module/Name',
            'InvoiceDesigner/Template/Module/ImageUpload',
            'InvoiceDesigner/Template/Module/ElementResizeMove'
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

    Service.FETCHED_STATE = 'fetch';
    Service.DUPLICATED_STATE = 'fetchAndDuplicate';
    Service.CREATED_STATE = 'create';

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
        this.getStorage().save(template);
        template.setState(Service.FETCHED_STATE)
            .setStateId(template.getId());
        this.getDomManipulator().hideSaveDiscardBar(template);
        return this;
    };

    Service.prototype.create = function()
    {
        var templateClass = require('InvoiceDesigner/Template/Entity');
        var template = new templateClass();
        template.setState(Service.CREATED_STATE);
        this.loadModules(template);
        this.getDomManipulator().hideSaveDiscardBar(template);
    };

    Service.prototype.duplicate = function(template)
    {
        template.setName('DUPLICATE - ' + template.getName())
            .setState(Service.DUPLICATED_STATE)
            .setStateId(template.getId())
            .setId();
        this.loadModules(template);
        this.getDomManipulator().hideSaveDiscardBar(template);
    };

    Service.prototype.fetchAndDuplicate = function(id)
    {
        var template = this.fetch(id);
        this.duplicate(template);
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

    Service.prototype.render = function(template)
    {
        var html = this.getMapper().toHtml(template);
        this.getDomManipulator().insertTemplateHtml(html);
        return this;
    };

    return new Service();
});
