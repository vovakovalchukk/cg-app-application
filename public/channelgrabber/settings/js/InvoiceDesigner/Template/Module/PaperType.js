define([
    'InvoiceDesigner/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/PaperType',
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/Template/PaperType/Storage/Ajax',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    paperTypeListener,
    templateService,
    paperTypeStorage,
    domManipulator
    ) {
    var PaperType = function()
    {
        ModuleAbstract.call(this);
        var service = templateService;
        var storage = paperTypeStorage;

        var template;
        var availablePaperTypes;

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
        this.getDomListener().init(this); // TODO this should be done automatically in module abstract CGIV-2026

        // Load paper type options from storage
        this.setAvailablePaperTypes(this.getStorage().fetchAll());
        domManipulator.populateCustomSelect('#paperTypeDropDown', this.getAvailablePaperTypes());

        // TODO show ui. Currently shown by default until CGIV-2002
    };

    PaperType.prototype.selectionMade = function(id)
    {
        // Look up paper type by id
        var selectedPaperType;
        this.getAvailablePaperTypes().some(function(paperType) {
            if (paperType.getId() === id) {
                selectedPaperType = paperType;
                return true;
            }
            return false;
        });

        this.getTemplate().getPage().setBackgroundImage(selectedPaperType.getBackgroundImage()); // TODO get getPage() method from somewhere
    };

    return new PaperType();
});