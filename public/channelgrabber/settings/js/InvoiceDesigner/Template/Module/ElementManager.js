define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/Application',
    'InvoiceDesigner/Template/Module/DomListener/ElementManager'
], function(
    ModuleAbstract,
    TemplateService,
    Application,
    ElementManagerListener
    ) {

    var ElementManager = function ()
    {
        ModuleAbstract.call(this);
        this.setDomListener(ElementManagerListener);
        ElementManagerListener.init(this);

        this.getService = function()
        {
            return TemplateService;
        };

        this.getApplication = function()
        {
            return Application;
        };
    };

    ElementManager.prototype = Object.create(ModuleAbstract.prototype);

    ElementManager.prototype.init = function(application, service)
    {
        ModuleAbstract.prototype.init.call(this, application, service);
    };

    ElementManager.prototype.addElementToCurrentTemplate = function(elementType)
    {
        var elementData = {};
        elementData.templateType = elementType;

        if (this.getApplication().getTemplate()) {
            var element = this.getService().getMapper().elementFromJson(elementData);
            this.getApplication().getTemplate().addElement(element, true);
        }
    }

    return new ElementManager();
});