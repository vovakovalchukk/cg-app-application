define([
    'require',
    'InvoiceDesigner/Template/Storage/Ajax',
    'InvoiceDesigner/Template/Mapper',
    'InvoiceDesigner/Template/DomManipulator',
    'InvoiceDesigner/Template/Module/AddDiscardBar'
    // Template Module requires here
], function(
    require,
    templateAjaxStorage,
    templateMapper,
    templateDomManipulator
    // Template Module variables here
) {
    var Service = function()
    {
        var storage = templateAjaxStorage;
        var mapper = templateMapper;
        var domManipulator = templateDomManipulator;

        var modules = [
            // Template Modules require() paths here
            'InvoiceDesigner/Template/Module/AddDiscardBar'
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
        var template = require('InvoiceDesigner/Template/Entity');
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
        this.getDomManipulator().hideSaveDiscardBar(template);
    };

    Service.prototype.fetchAndDuplicate = function(id)
    {
        var template = this.fetch(id);
        this.duplicate(template);
    };

    Service.prototype.showAsPdf = function(template)
    {
        /*
         * TODO (CGIV-2011)
         */
    };

    Service.prototype.loadModules = function(template)
    {
        var modules = this.getModules();
        for (var key in modules) {
            var module = require(modules[key]);
            module.init(template);
        }
    };

    Service.prototype.render = function(template)
    {
        /*
         * TODO (CGIV-2026)
         * html = Mapper::toHtml(template)
         * DomManipulator::insertTemplateHtml(html);
         */
    };

    return new Service();
});