define([
    'require',
    'InvoiceDesigner/Template/Storage/Ajax',
    'InvoiceDesigner/Template/Mapper',
    'InvoiceDesigner/Template/DomManipulator'
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

    Service.prototype.fetch = function(id)
    {
        if (!id) {
            throw 'InvalidArgumentException: InvoiceDesigner\Template\Service::fetch must be passed a template ID';
        }
        var template = this.getStorage().fetch(id);
        template.setState(this.LOADED_STATE);
        return template;
    };

    Service.prototype.save = function(template)
    {
        this.getStorage().save(template);
        return this;
    };

    Service.prototype.create = function()
    {
        var template = require('InvoiceDesigner/Template/Entity');
        template.setState(this.NEW_STATE);
        this.loadModules(template);
    };

    Service.prototype.duplicate = function(template)
    {
        template.setName('DUPLICATE - ' + template.getName());
        template.setId();
        template.setState(this.DUPLICATED_STATE);
        this.render(template);
        this.notifyOfChange(template);
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

    Service.prototype.notifyOfChange = function(template)
    {
        this.getDomManipulator().triggerTemplateChangeEvent(template);
        return this;
    };

    Service.prototype.LOADED_STATE = 'loaded';
    Service.prototype.DUPLICATED_STATE = 'duplicate';
    Service.prototype.NEW_STATE = 'new';


    return new Service();
});