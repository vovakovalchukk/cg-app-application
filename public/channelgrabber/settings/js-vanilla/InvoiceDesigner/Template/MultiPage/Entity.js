define([
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/EntityAbstract',
    'InvoiceDesigner/PubSubAbstract',
    'InvoiceDesigner/Constants',
    'InvoiceDesigner/utility'
], function(
    templateService,
    EntityHydrateAbstract,
    EntityAbstract,
    PubSubAbstract,
    Constants,
    utility
) {
    let {DIMENSION_TO_GRID_TRACK, GRID_TRACK_TO_DIMENSION} = Constants;

    const Entity = function() {
        EntityHydrateAbstract.call(this);
        EntityAbstract.call(this);
        PubSubAbstract.call(this);

        this.subscribeToTopic(this.getTopicNames().paperSpace, updateDimensionsToMaxValues.bind(this));

        let data = {
            rows: 1,
            columns: 1,
            width: null,
            height: null
        };
        let workableAreaIndicatorElement = null;

        this.getData = function() {
            return data;
        };

        this.render = function(template, templatePageElement) {
            this.renderWorkableAreaIndicator(template, templatePageElement);
            this.renderMultiPageGuidelines(template, templatePageElement);
        };

        this.getEntityName = function() {
            return 'MultiPage';
        };

        this.renderWorkableAreaIndicator = function(template, templatePageElement) {
            let workableAreaIndicatorElement = this.createWorkableAreaIndicator(template);
            templatePageElement.prepend(workableAreaIndicatorElement);
            this.setWorkableAreaIndicatorElement(workableAreaIndicatorElement);
            return workableAreaIndicatorElement;
        };

        this.renderMultiPageGuidelines = function(template, templatePageElement) {
            const paperPage = template.getPaperPage();
            const printPage = template.getPrintPage();

            const measurementUnit = paperPage.getMeasurementUnit();

            let gridContainer = document.createElement('div');
            gridContainer.className = 'template-multi-page-guidelines-container-element';
            gridContainer.style.height = printPage.getHeight(template) + measurementUnit;
            gridContainer.style.width = printPage.getWidth(template) + measurementUnit;
            gridContainer.style.gridTemplateColumns = 'minmax(0, 1fr) '.repeat(Math.floor(this.get('columns')));
            gridContainer.style.top = printPage.getMargin('top') + measurementUnit;
            gridContainer.style.left = printPage.getMargin('left') + measurementUnit;
            gridContainer.style.bottom = printPage.getMargin('bottom') + measurementUnit;
            gridContainer.style.right = printPage.getMargin('right') + measurementUnit;

            let numberOfCells = Math.floor(this.get('columns')) * Math.floor(this.get('rows'));
            for (let i = 0; i < numberOfCells; i++) {
                let cell = document.createElement('div');
                cell.className = 'template-multi-page-guidelines-cell-element';
                gridContainer.prepend(cell);
            }

            templatePageElement.prepend(gridContainer);
        };

        this.getDimensionForWorkableAreaIndicator = function(template, dimension) {
            const multiPageDimensionValue = this.getData()[dimension];
            if (multiPageDimensionValue) {
                return multiPageDimensionValue;
            }
            return this.getDimensionValueToBeRelativeTo(template, dimension);
        };

        this.createWorkableAreaIndicator = function(template) {
            const paperPage = template.getPaperPage();
            const printPage = template.getPrintPage();

            const measurementUnit = paperPage.getMeasurementUnit();

            let element = document.createElement('div');

            let height = this.getDimensionForWorkableAreaIndicator(template, 'height') + measurementUnit;
            let width = this.getDimensionForWorkableAreaIndicator(template, 'width') + measurementUnit;

            let top = printPage.getMargin('top') + measurementUnit;
            let left = printPage.getMargin('left') + measurementUnit;

            element.id = 'workableAreaIndicator';
            element.className = 'template-workable-area-indicator-element';
            element.style.height = height;
            element.style.width = width;
            element.style.top = top;
            element.style.left = left;

            return element;
        };

        this.setWorkableAreaIndicatorElement = function(newElement) {
            workableAreaIndicatorElement = newElement;
        };

        this.getHeight = function() {
            return data['height'];
        };

        this.getWidth = function() {
            return data['width'];
        };

        this.getGridTrack = function(gridTrack) {
            return data[gridTrack];
        };

        this.setDimension = function(dimension, value) {
            this.set(dimension, value);
        };

        this.getRelevantDimensionFromGridTrack = function(gridTrackProperty) {
            return GRID_TRACK_TO_DIMENSION[gridTrackProperty];
        };

        this.setMultiple = function(fields, populating) {
            let oldData = Object.assign({}, data);
            data = {
                ...oldData,
                ...fields
            };

            if (populating) {
                return;
            }
            this.publish();
        };

        function updateDimensionsToMaxValues(publishSettings) {
            let {template, dimensionAffected, populating} = publishSettings;
            const multiPage = template.getMultiPage();
            const gridTrackValue = multiPage.getGridTrack(Constants.DIMENSION_TO_GRID_TRACK[dimensionAffected]);

            let maxValue = this.calculateMaxDimensionValue(template, dimensionAffected, gridTrackValue);

            this.set(dimensionAffected, maxValue, populating);

            publishSettings.recordEntityUpdate({
                entity: this.getEntityName(),
                field: dimensionAffected,
                value: maxValue
            })
        }
    };

    Entity.prototype = Object.create(utility.createPrototype([
        EntityHydrateAbstract,
        PubSubAbstract,
        EntityAbstract
    ]));

    Entity.prototype.getDimensionValueToBeRelativeTo = function(template, dimension) {
        const paperPage = template.getPaperPage();
        const printPage = template.getPrintPage();

        let printPageDimension;
        let paperPageDimension;

        if (dimension === 'width') {
            paperPageDimension = paperPage.getWidth();
            printPageDimension = printPage.getWidth(template);
        } else {
            paperPageDimension = paperPage.getHeight();
            printPageDimension = printPage.getHeight(template);
        }

        return printPageDimension ? printPageDimension : paperPageDimension;
    };

    Entity.prototype.calculateMaxDimensionValue = function(template, dimension, gridTrackValue) {
        let gridTrackProperty = DIMENSION_TO_GRID_TRACK[dimension];

        let relevantDimension = GRID_TRACK_TO_DIMENSION[gridTrackProperty];

        let dimensionValueToBeRelativeTo = this.getDimensionValueToBeRelativeTo(template, relevantDimension);

        gridTrackValue = parseInt(gridTrackValue);

        if (!gridTrackValue) {
            return dimensionValueToBeRelativeTo;
        }

        let maxDimension = dimensionValueToBeRelativeTo / gridTrackValue;

        return maxDimension;
    };

    Entity.prototype.getGridTrackValueFromDimension = function(template, dimension, dimensionValue) {
        if (!dimensionValue) {
            return 1;
        }
        let maximumArea = this.getDimensionValueToBeRelativeTo(template, dimension);
        let gridTrackValue = maximumArea / dimensionValue;
        return gridTrackValue;
    };

    Entity.prototype.toJson = function(template) {
        let data = Object.assign({}, this.getData());

        delete data.rows;
        delete data.columns;

        let width = null;
        let height = null;
        if (data.width || data.height) {
            width = data.width || this.getDimensionValueToBeRelativeTo(template, 'width');
            height = data.height || this.getDimensionValueToBeRelativeTo(template, 'height');
        }

        data = {
            ...data,
            width,
            height
        };

        let json = JSON.parse(JSON.stringify(data));
        return json;
    };

    return Entity;
});