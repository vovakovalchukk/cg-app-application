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
        this.setAvailablePaperTypes(this.getStorage().fetchAll());
        domManipulator.populateCustomSelect('#paperTypeDropdown', this.getAvailablePaperTypes());

        // TODO show ui. Currently shown by default until CGIV-2002. Should still implement being shown on module load though.
    };

    PaperType.prototype.selectionMade = function(id, isInverse)
    {
        var selectedPaperType;
        this.getAvailablePaperTypes().some(function(paperType) {
            if (paperType.getId() == id) {
                selectedPaperType = paperType;
                return true;
            }
            return false;
        });

        if (typeof selectedPaperType === 'undefined') {
            throw 'InvalidSelectionException: InvoiceDesigner/Template/Module/PaperType.selectionMade() received an id which does not exist';
        }

        var backgroundImage = isInverse ? selectedPaperType.getBackgroundImageInverse() : selectedPaperType.getBackgroundImage();
        console.log("BackgroundImageUrl: " + backgroundImage);
        //this.getTemplate().getPage().setBackgroundImage(selectedPaperType.getBackgroundImage()); // TODO get getPage() method from CGIV-2026
    };

    return new PaperType();
});