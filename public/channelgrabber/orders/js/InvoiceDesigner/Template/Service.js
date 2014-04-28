define([
    'require',
    './Storage/Ajax',
    './Mapper',
    './DomManipulator'
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
        /*
         * TODO (CGIV-2009)
         */
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
         * TODO (CGIV-2009)
         */
    };

    Service.prototype.loadModules = function(template)
    {
        /*
         * TODO (CGIV-2009)
         * Use require() to create modules for this template
         */
    };

    Service.prototype.render = function(template)
    {
        /*
         * TODO (CGIV-2009)
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