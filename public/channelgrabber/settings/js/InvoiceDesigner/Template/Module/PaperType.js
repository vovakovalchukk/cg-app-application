define([
    'InvoiceDesigner/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/PaperType',
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/Template/PaperType/Storage/Ajax'
], function(
    ModuleAbstract,
    paperTypeListener,
    templateService,
    paperTypeStorage
    ) {
    var PaperType = function()
    {
        ModuleAbstract.call(this);
        var service = templateService;
        var storage = paperTypeStorage;

        var template;
        var availablePaperTypes;

        var hasPaperType = function(id) {
            this.getAvailablePaperTypes().forEach(function(paperType) { // TODO WHY INVALID?!

            });
        };

        this.setDomListener(paperTypeListener);

        this.getService = function()
        {
            return service;
        };

        this.getStorage = function()
        {
            return storage;
        }

        this.setTemplate = function(newTemplate)
        {
            template = newTemplate;
        };

        this.getTemplate = function()
        {
            return template;
        };

        this.setAvailablePaperTypes = function(newAvailablePaperTypes)
        {
            availablePaperTypes = newAvailablePaperTypes;
        }

        this.getAvailablePaperTypes = function()
        {
            return availablePaperTypes;
        }
    };

    PaperType.prototype = Object.create(ModuleAbstract.prototype);

    PaperType.prototype.init = function(application)
    {
        ModuleAbstract.prototype.init.call(this, application);
        this.getDomListener().init(this);

        // TODO Load paper type options from storage
        this.setAvailablePaperTypes(this.getStorage().fetchAll());

        // TODO show ui. Currently shown by default until CGIV-2002
    };

    PaperType.prototype.selectionMade = function(id)
    {
        // TODO Look up paper type by id
        var selectedPaperType;
        this.getAvailablePaperTypes().some(function(paperType) {
            if (paperType.getId() === id) {
                selectedPaperType = paperType;
                return true;
            }
            return false;
        });

        // TODO template.getPage().setBackgroundImage(paperTypeById.getBackgroundImage())
        this.getTemplate().getPage().setBackgroundImage(selectedPaperType.getBackgroundImage()); // TODO get getPage() method from somewhere
    };

    return new PaperType();
});