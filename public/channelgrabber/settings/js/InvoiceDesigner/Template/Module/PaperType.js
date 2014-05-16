define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/PaperType',
    'InvoiceDesigner/Template/PaperType/Storage/Ajax',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    PaperTypeListener,
    paperTypeStorage,
    domManipulator
    ) {
    var PaperType = function()
    {
        ModuleAbstract.call(this);
        var storage = paperTypeStorage;

        var template;
        var availablePaperTypes;

        this.setDomListener(new PaperTypeListener());

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
        domManipulator.show("#" + PaperTypeListener.CONTAINER_ID);
        domManipulator.populateCustomSelect(paperTypeDropdownId, this.getAvailablePaperTypes());
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
        this.getTemplate().getPage().setHeight(selectedPaperType.getHeight());
        this.getTemplate().getPage().setWidth(selectedPaperType.getWidth());
        this.getTemplate().getPage().setBackgroundImage(backgroundImage);
    };

    return new PaperType();
});