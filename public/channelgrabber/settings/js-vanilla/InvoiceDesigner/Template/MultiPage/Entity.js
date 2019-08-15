define([
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/PubSubAbstract'
], function(
    templateService,
    EntityHydrateAbstract,
    PubSubAbstract
) {
    const DIMENSION_TO_TRACK = {
        height: 'rows',
        width: 'columns'
    };
    const TRACK_TO_DIMENSION = getKeyValueReverse(DIMENSION_TO_TRACK);

    const Entity = function() {
        EntityHydrateAbstract.call(this);
        PubSubAbstract.call(this);

        let data = {
            rows: null,
            columns: null,
            width: null,
            height: null
        };
        let workableAreaIndicatorElement = null;

        this.getData = function() {
            return data;
        };

        this.render = function(template, templatePageElement) {
            this.renderWorkableAreaIndicator(template, templatePageElement);
        };

        this.renderWorkableAreaIndicator = function(template, templatePageElement) {
            let workableAreaIndicatorElement = this.createWorkableAreaIndicator(template);
            templatePageElement.prepend(workableAreaIndicatorElement);
            this.setWorkableAreaIndicatorElement(workableAreaIndicatorElement);
        };

        this.createWorkableAreaIndicator = function(template) {
            const paperPage = template.getPaperPage();
            const printPage = template.getPrintPage();

            const measurementUnit = paperPage.getMeasurementUnit();

            let element = document.createElement('div');

            let height = this.getHeight() + measurementUnit;
            let width = this.getWidth() + measurementUnit;
            let top = printPage.getMargin('top') + measurementUnit;
            let left = printPage.getMargin('left') + measurementUnit;

            //todo - apply top and left based on printPage left and top margins
            element.id = 'workableAreaIndicator';
            element.className = 'test';
            element.style.height = height;
            element.style.width = width;
            element.style.top = top;
            element.style.left = left;
            element.style.border = '1px sold red';
            element.style.boxSizing = 'border-box';
            element.style.position = 'relative';
            element.style.background = 'none';
            element.style.zIndex = '10';
            element.style.boxShadow = 'rgba(137, 137, 137, 0.3) 0px 0px 0px 1000in';
            element.style.pointerEvents = 'none';
//            element.style = {
//                height: '300px',
//                width: '300px',
//                border: '1px solid red',
//                boxSizing: 'border-box'
//            };
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

        this.setDimension = function(dimension, value) {
            this.set(dimension, value);
        };

        this.getTrackValue = function(track) {
            return data[track];
        };

        this.getRelevantDimensionFromTrack = function(trackProperty) {
            return TRACK_TO_DIMENSION[trackProperty];
        };

        this.get = function(field) {
            return data[field];
        };

        this.setMultiple = function(fields, populating) {
            console.log('in multiSet');
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

        this.set = function(field, value, populating) {
            console.log('in set ', {field, value});

            data[field] = value;

            if (populating) {
                return;
            }

            this.publish();
        };
    };

    let combinedPrototype = createPrototype();

    Entity.prototype = Object.create(combinedPrototype);

    Entity.prototype.getDimensionValueToBeRelativeTo = function(template, dimension) {
        const paperPage = template.getPaperPage();
        const printPage = template.getPrintPage();

        let printPageDimension;
        let paperPageDimension;

        if (dimension === 'width') {
            paperPageDimension = paperPage.getWidth();
            printPageDimension = printPage.getWidth();
        } else {
            paperPageDimension = paperPage.getHeight();
            printPageDimension = printPage.getHeight();
        }

        return printPageDimension ? printPageDimension : paperPageDimension;
    };

    Entity.prototype.calculateMaxDimensionValue = function(template, dimension, trackValue) {
        let trackProperty = DIMENSION_TO_TRACK[dimension];

        let relevantDimension = TRACK_TO_DIMENSION[trackProperty];

        let dimensionValueToBeRelativeTo = this.getDimensionValueToBeRelativeTo(template, relevantDimension);

        let maxDimension = Math.floor(dimensionValueToBeRelativeTo / trackValue);

        return maxDimension;
    };

    Entity.prototype.toJson = function() {
        let data = Object.assign({}, this.getData());
        let json = JSON.parse(JSON.stringify(data));
        return json;
    };

    return Entity;

    function createPrototype() {
        let combinedPrototype = EntityHydrateAbstract.prototype;
        for (var key in PubSubAbstract.prototype) {
            combinedPrototype[key] = PubSubAbstract.prototype[key];
        }
        return combinedPrototype;
    }

    function getKeyValueReverse(forwardObject) {
        let reversed = {};
        for (let key in forwardObject) {
            if (forwardObject.hasOwnProperty(key)) {
                reversed[forwardObject[key]] = key;
            }
        }
        return reversed;
    }
});