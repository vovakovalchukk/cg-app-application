define([
    'require',
    'InvoiceDesigner/Template/Storage/Ajax',
    'InvoiceDesigner/Template/Mapper',
    'InvoiceDesigner/Template/DomManipulator',
    // Template Module requires here
    'InvoiceDesigner/Template/Module/InspectorManager',
    'InvoiceDesigner/Template/Module/Renderer'
], function(
    require,
    templateAjaxStorage,
    templateMapper,
    templateDomManipulator
) {
    var Service = function()
    {
        var storage = templateAjaxStorage;
        var mapper = templateMapper;
        var domManipulator = templateDomManipulator;

        var modules = [
            // Template Modules require() paths here
            'InvoiceDesigner/Template/Module/InspectorManager',
            'InvoiceDesigner/Template/Module/Renderer'
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

        /*
         * TODO (CGIV-2002)
         */
    };

    Service.prototype.save = function(template)
    {
        this.getStorage().save(template);
        return this;
    };

    Service.prototype.create = function()
    {
        /*
         * TODO (CGIV-2002)
         */
    };

    Service.prototype.duplicate = function(template)
    {
        /*
         * TODO (CGIV-2002)
         */
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
            module.init(template, this);
        }
    };

    Service.prototype.render = function(template)
    {
        var html = this.getMapper().toHtml(template);
        this.getDomManipulator().insertTemplateHtml(html);
        return this;
    };

    Service.prototype.notifyOfChange = function(template)
    {
        this.getDomManipulator().triggerTemplateChangeEvent(template);
        return this;
    };

    return new Service();
});