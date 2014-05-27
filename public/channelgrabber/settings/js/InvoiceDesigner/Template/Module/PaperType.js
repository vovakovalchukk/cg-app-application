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

        var availablePaperTypes;

        this.setDomListener(new PaperTypeListener());

        this.getStorage = function()
        {
            return storage;
        }

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
        domManipulator.populateCustomSelect(
            paperTypeDropdownId, this.getAvailablePaperTypes(), template.getPaperPage().getPaperType()
        );
        this.selectionMade(template.getPaperPage().getPaperType());
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
        var templatePage = this.getTemplate().getPaperPage();

        templatePage.setHeight(selectedPaperType.getHeight());
        templatePage.setWidth(selectedPaperType.getWidth());
        templatePage.setBackgroundImage(backgroundImage);
        templatePage.setPaperType(selectedPaperType.getId());
    };

    return new PaperType();
});