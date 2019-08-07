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
    const measurementUnits = [
        {title: 'mm', value: 'mm'},
        {title: 'in', value: 'in'}
    ];

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

        let currentMeasurementUnit = paperPage.getMeasurementUnit() || PaperType.DEFAULT_MEASUREMENT_UNIT;
        paperPage.setMeasurementUnit(currentMeasurementUnit, true);
        this.populateMeasurementUnitSelect(currentMeasurementUnit);

        this.paperTypeSelectionMade(currentPaperType, true);
    };

    PaperType.prototype.populateMeasurementUnitSelect = function(selected) {
        domManipulator.populateCustomSelect(
            Constants.MEASUREMENT_UNIT_DROPDOWN_ID,
            measurementUnits,
            selected,
            {
                sizeClass: 'small'
            }
        );
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
        paperPage.setMeasurementUnit(value);
    };

    PaperType.prototype.paperTypeSelectionMade = function(id, initialise) {
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

        let paperPage = this.getTemplate().getPaperPage();

        let height = selectedPaperType.getHeight();
        let width = selectedPaperType.getWidth();
        let paperTypeId = selectedPaperType.getId()

        setPaperDimensionFields({height, width});

        if (initialise) {
            paperPage.set('height', height, true);
            paperPage.set('width', width, true);
            paperPage.set('paperType', paperTypeId, true);
            return;
        }

        paperPage.setMeasurementUnit('mm');
        this.populateMeasurementUnitSelect('mm');

        paperPage.setHeight(height);
        paperPage.setWidth(width);
        paperPage.setPaperType(paperTypeId);
    };

    return new PaperType();

    function setPaperDimensionFields({height, width}) {
        let heightInput = document.getElementById('paperHeight');
        let widthInput = document.getElementById('paperWidth');

        heightInput.value = height;
        widthInput.value = width;
    }
});