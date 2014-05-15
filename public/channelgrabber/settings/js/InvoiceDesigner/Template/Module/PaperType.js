define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/PaperType',
    'InvoiceDesigner/Template/PaperType/Storage/Ajax',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    paperTypeListener,
    paperTypeStorage,
    domManipulator
    ) {
    var PaperType = function()
    {
        ModuleAbstract.call(this);
        var storage = paperTypeStorage;

        var template;
        var availablePaperTypes;

        this.setDomListener(new paperTypeListener());

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

    PaperType.prototype.init = function(template, templateService)
    {
        ModuleAbstract.prototype.init.call(this, template, templateService);
        this.setAvailablePaperTypes(this.getStorage().fetchAll());
        domManipulator.show("#" + paperTypeListener.ID);
        domManipulator.populateCustomSelect('#paperTypeDropdown', this.getAvailablePaperTypes());
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