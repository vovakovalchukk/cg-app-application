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

    PaperType.DEFAULT_ID = 1;

    PaperType.prototype = Object.create(ModuleAbstract.prototype);

    PaperType.prototype.init = function(template, templateService)
    {
        ModuleAbstract.prototype.init.call(this, template, templateService);
        this.setAvailablePaperTypes(this.getStorage().fetchAll());
        domManipulator.show("#" + PaperTypeListener.CONTAINER_ID);
        var currentPaperType = template.getPaperPage().getPaperType() || PaperType.DEFAULT_ID;
        domManipulator.populateCustomSelect(
            paperTypeDropdownId, this.getAvailablePaperTypes(), currentPaperType
        );
        this.selectionMade(currentPaperType, false, true);
    };

    PaperType.prototype.selectionMade = function(id, isInverse, initialise)
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

        if (initialise) {
            templatePage.set('height', selectedPaperType.getHeight(), true);
            templatePage.set('width', selectedPaperType.getWidth(), true);
            templatePage.set('backgroundImage', backgroundImage, true);
            templatePage.set('paperType', selectedPaperType.getId(), true);
            templatePage.set('inverse', isInverse, true);
            return;
        }
        templatePage.setHeight(selectedPaperType.getHeight());
        templatePage.setWidth(selectedPaperType.getWidth());
        templatePage.setBackgroundImage(backgroundImage);
        templatePage.setPaperType(selectedPaperType.getId());
        templatePage.setInverse(isInverse);
    };

    return new PaperType();
});