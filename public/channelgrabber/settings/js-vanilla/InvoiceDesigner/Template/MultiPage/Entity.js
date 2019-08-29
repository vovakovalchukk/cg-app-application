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
    let {DIMENSION_TO_TRACK, TRACK_TO_DIMENSION} = Constants;

    const Entity = function() {
        EntityHydrateAbstract.call(this);
        EntityAbstract.call(this);
        PubSubAbstract.call(this);

        this.subscribeToTopic(this.getTopicNames().paperSpace, updateDimensionsToMaxValues.bind(this));

        let data = {
            rows: null,
            columns: null,
            width: null,
            height: null,
            visibility: false
        };
        let workableAreaIndicatorElement = null;

        this.getData = function() {
            return data;
        };

        this.render = function(template, templatePageElement) {
            this.setVisibilityFromData(this.getHeight(), this.getWidth());
            this.renderWorkableAreaIndicator(template, templatePageElement);
            this.renderMultiPageGuidelines(template, templatePageElement);
        };

        this.getEntityName = function() {
            return 'MultiPage';
        };

        this.setVisibilityFromData = function(height, width) {
            if (!height || !width) {
                this.setVisiblity(false);
                return;
            }
            this.setVisiblity(true);
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

        this.createWorkableAreaIndicator = function(template) {
            const paperPage = template.getPaperPage();
            const printPage = template.getPrintPage();

            const measurementUnit = paperPage.getMeasurementUnit();

            let element = document.createElement('div');
            let visibility = this.getVisibility();

            let height = this.getHeight(template) + measurementUnit;
            let width = this.getWidth(template) + measurementUnit;

            let top = printPage.getMargin('top') + measurementUnit;
            let left = printPage.getMargin('left') + measurementUnit;

            element.id = 'workableAreaIndicator';
            element.className = 'template-workable-area-indicator-element';
            element.style.height = height;
            element.style.width = width;
            element.style.top = top;
            element.style.left = left;
            element.style.visibility = visibility ? 'visible' : 'hidden';

            return element;
        };

        this.setWorkableAreaIndicatorElement = function(newElement) {
            workableAreaIndicatorElement = newElement;
        };

        this.setVisiblity = function(value) {
            data['visibility'] = value;
        };

        this.getVisibility = function() {
            return data['visibility'];
        };

        this.getHeight = function() {
            return data['height'];
        };

        this.getWidth = function() {
            return data['width'];
        };

        this.getTrack = function(track) {
            return data[track];
        };

        this.setDimension = function(dimension, value) {
            this.set(dimension, value);
        };

        this.getRelevantDimensionFromTrack = function(trackProperty) {
            return TRACK_TO_DIMENSION[trackProperty];
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
            const trackValue = multiPage.getTrack(Constants.DIMENSION_TO_TRACK[dimensionAffected]);

            let maxValue = this.calculateMaxDimensionValue(template, dimensionAffected, trackValue);

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

    Entity.prototype.calculateMaxDimensionValue = function(template, dimension, trackValue) {
        let trackProperty = DIMENSION_TO_TRACK[dimension];

        let relevantDimension = TRACK_TO_DIMENSION[trackProperty];

        let dimensionValueToBeRelativeTo = this.getDimensionValueToBeRelativeTo(template, relevantDimension);

        trackValue = parseInt(trackValue);

        if (!trackValue) {
            return dimensionValueToBeRelativeTo;
        }

        let maxDimension = dimensionValueToBeRelativeTo / trackValue;

        return maxDimension;
    };

    Entity.prototype.getTrackValueFromDimension = function(template, dimension, dimensionValue) {
        if (!dimensionValue) {
            return;
        }
        let maximumArea = this.getDimensionValueToBeRelativeTo(template, dimension);
        let trackValue = maximumArea / dimensionValue;
        return trackValue;
    };

    Entity.prototype.toJson = function() {
        let data = Object.assign({}, this.getData());
        delete data.rows;
        delete data.columns;
        delete data.visibility;
        if (!data.width || !data.height) {
            return {};
        }
        let json = JSON.parse(JSON.stringify(data));
        return json;
    };

    return Entity;
});