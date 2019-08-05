define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/PaperType',
    'InvoiceDesigner/Template/PaperType/Storage/Ajax',
    'InvoiceDesigner/Template/DomManipulator',
    'InvoiceDesigner/Constants'
], function(
    ModuleAbstract,
    PaperTypeListener,
    paperTypeStorage,
    domManipulator,
    Constants
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

    PaperType.DEFAULT_PAPER_TYPE_ID = 1;
    PaperType.PAPERTYPE_CHECKBOX = '#inverseLabelPosition';
    PaperType.DEFAULT_MEASUREMENT_UNIT = 'mm';

    PaperType.prototype = Object.create(ModuleAbstract.prototype);

    PaperType.prototype.init = function(template, templateService) {
        const paperPage = template.getPaperPage();
        ModuleAbstract.prototype.init.call(this, template, templateService);

        let fetched = this.getStorage().fetchAll();
        this.setAvailablePaperTypes(fetched);

        domManipulator.show("#" + PaperTypeListener.CONTAINER_ID);
        var currentPaperType = paperPage.getPaperType() || PaperType.DEFAULT_PAPER_TYPE_ID;

        domManipulator.populatePaperTypeSelect(
            Constants.PAPER_TYPE_DROPDOWN_ID,
            this.getAvailablePaperTypes(),
            currentPaperType
        );

        var currentInverseCheckbox = paperPage.getInverse();
        domManipulator.changeCheckBoxState(
            PaperType.PAPERTYPE_CHECKBOX,
            currentInverseCheckbox
        );

        var currentMeasurementUnit = paperPage.getMeasurementUnit() || PaperType.DEFAULT_MEASUREMENT_UNIT;
        let measurementUnits = [{}, {}];
        measurementUnits[0].title = measurementUnits[0].value = 'mm';
        measurementUnits[1].title = measurementUnits[1].value = 'in';
        domManipulator.populateCustomSelect(
            Constants.MEASUREMENT_UNIT_DROPDOWN_ID,
            measurementUnits,
            currentMeasurementUnit,
            {
                sizeClass: 'small',
            }
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

    PaperType.prototype.changeMeasurementUnit = function(value) {
        let paperPage = this.getTemplate().getPaperPage();
        console.log('in changeMeasurementUnit')
        paperPage.setMeasurementUnit(value);
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