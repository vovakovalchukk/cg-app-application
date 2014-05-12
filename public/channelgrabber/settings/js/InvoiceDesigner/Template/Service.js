define([
    'require',
    'InvoiceDesigner/Template/Storage/Ajax',
    'InvoiceDesigner/Template/Mapper',
    'InvoiceDesigner/Template/DomManipulator',
    'InvoiceDesigner/Template/PaperType'
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
            'InvoiceDesigner/Template/PaperType'
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

    return new Service();
});