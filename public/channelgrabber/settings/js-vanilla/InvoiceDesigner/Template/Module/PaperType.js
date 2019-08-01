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

        this.paperTypeSelectionMade(currentPaperType, currentInverseCheckbox, true);
    };

    PaperType.prototype.changePaperDimension = function(property, newValue) {
        let paperPage = this.getTemplate().getPaperPage();
        if (property === 'height') {
            paperPage.setHeight(newValue);
            return;
        }
        paperPage.setWidth(newValue);
    };

    PaperType.prototype.paperTypeSelectionMade = function(id, isInverse, initialise) {
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


        // todo - guard clause selectedPaperType
        var backgroundImage = isInverse ? selectedPaperType.getBackgroundImageInverse() : selectedPaperType.getBackgroundImage();
        let paperPage = this.getTemplate().getPaperPage();

        let height = selectedPaperType.getHeight();
        let width = selectedPaperType.getWidth();
        let paperTypeId = selectedPaperType.getId()

        //todo - set values of input
        setPaperDimensionFields({height, width});

        //todo - move these setters somewhere else ... setProp that looks at initialise
        if (initialise) {
            paperPage.set('height', height, true);
            paperPage.set('width', width, true);
            paperPage.set('backgroundImage', backgroundImage, true);
            paperPage.set('paperType', paperTypeId, true);
            paperPage.set('inverse', isInverse, true);
            return;
        }
        paperPage.setHeight(height);
        paperPage.setWidth(width);
        paperPage.setBackgroundImage(backgroundImage);
        paperPage.setPaperType(paperTypeId);
        paperPage.setInverse(isInverse);
    };

    return new PaperType();

    function setPaperDimensionFields({height, width}) {
        let heightInput = document.getElementById('paperHeight');
        let widthInput = document.getElementById('paperWidth');

        heightInput.value = height;
        widthInput.value = width;
    }
});