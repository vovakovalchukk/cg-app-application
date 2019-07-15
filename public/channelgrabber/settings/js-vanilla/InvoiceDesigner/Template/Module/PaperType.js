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
    var PaperType = function() {
        ModuleAbstract.call(this);
        var storage = paperTypeStorage;

        var availablePaperTypes;

        this.setDomListener(new PaperTypeListener());

        this.getStorage = function() {
            return storage;
        };

        this.setAvailablePaperTypes = function(newAvailablePaperTypes) {
            availablePaperTypes = newAvailablePaperTypes;
        };

        this.getAvailablePaperTypes = function() {
            return availablePaperTypes;
        };
    };

    PaperType.DEFAULT_ID = 1;
    PaperType.PAPERTYPE_CHECKBOX = '#inverseLabelPosition';

    PaperType.prototype = Object.create(ModuleAbstract.prototype);

    PaperType.prototype.init = function(template, templateService) {
        ModuleAbstract.prototype.init.call(this, template, templateService);

        let fetched = this.getStorage().fetchAll();
        this.setAvailablePaperTypes(fetched);

        domManipulator.show("#" + PaperTypeListener.CONTAINER_ID);
        var currentPaperType = template.getPaperPage().getPaperType() || PaperType.DEFAULT_ID;
        domManipulator.populateCustomSelect(
            paperTypeDropdownId, this.getAvailablePaperTypes(), currentPaperType
        );
        var currentInverseCheckbox = template.getPaperPage().getInverse();
        domManipulator.changeCheckBoxState(
            PaperType.PAPERTYPE_CHECKBOX,
            currentInverseCheckbox
        );

        this.selectionMade(currentPaperType, currentInverseCheckbox, true);
    };

    PaperType.prototype.changePaperDimension = function(property, newValue) {
        let templatePage = this.getTemplate().getPaperPage();
        if (property === 'height') {
            templatePage.setHeight(newValue);
            return;
        }
        templatePage.setWidth(newValue);
    };

    PaperType.prototype.selectionMade = function(id, isInverse, initialise) {
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

        let height = selectedPaperType.getHeight();
        let width = selectedPaperType.getWidth();
        let paperTypeId = selectedPaperType.getId()

        //todo - set values of input
        setPaperDimensionFields({height, width});

        //todo - move these setters somewhere else ... setProp that looks at initialise
        if (initialise) {
            templatePage.set('height', height, true);
            templatePage.set('width', width, true);
            templatePage.set('backgroundImage', backgroundImage, true);
            templatePage.set('paperType', paperTypeId, true);
            templatePage.set('inverse', isInverse, true);
            return;
        }
        templatePage.setHeight(height);
        templatePage.setWidth(width);
        templatePage.setBackgroundImage(backgroundImage);
        templatePage.setPaperType(paperTypeId);
        templatePage.setInverse(isInverse);
    };

    return new PaperType();

    function setPaperDimensionFields({height, width}) {
        console.log('in setDimensionFields');
        let heightInput = document.getElementById('paperHeight');
        let widthInput = document.getElementById('paperWidth');

        heightInput.value = height;
        widthInput.value = width;
        console.log('{heightInput,widthInput}: ', {heightInput, widthInput});
    }
});