define([
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/PubSubAbstract'
], function(
    templateService,
    EntityHydrateAbstract,
    PubSubAbstract
) {

    function getWidth() {
        let multiPageWidth = this.getWidth();
        if (!multiPageWidth) {
            //            calculateMaxDimensionFromTrackValue
//            let topMargin = printPage.getMargin('top');
//            let bottomMargin = printPage.getMargin('bottom');
//
//            let paperHeight = paperPage.getHeight();
            return;
        }
        return multiPageWidth;
    }

    const DIMENSION_TO_TRACK = {
        height: 'rows',
        width: 'columns'
    };

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
            let data = this.getData();
            // initialise workable area element
            console.log('in multipage render');
            let workableAreaIndicatorElement = this.createWorkableAreaIndicator(template);
            templatePageElement.prepend(workableAreaIndicatorElement);
            this.setWorkableAreaIndicatorElement(workableAreaIndicatorElement);
        };

        this.createWorkableAreaIndicator = function(template) {
            const paperPage = template.getPaperPage();
            const printPage = template.getPrintPage();
            console.log('in createWorkableArea...', data);

            let element = document.createElement('div');
            element.id = 'workableAreaIndicator';
            element.className = 'test';
            element.style.height = '300px';
            element.style.width = '300px';
            element.style.border = '1px sold red';
            element.style.boxSizing = 'border-box';
            element.style.position = 'relative';
            element.style.background = 'none';
            element.style.zIndex = '10';
            element.style.boxShadow = 'rgba(0, 0, 0, 0.3) 0px 0px 0px 1000px';
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

        this.getTrackValue = function(track) {
            return data[track];
        };

        this.get = function(field) {
            return data[field];
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

    Entity.prototype.calculateMaxDimensionValue = function(template, dimension, ajaxJson) {
        let relevantTrackProperty = DIMENSION_TO_TRACK[dimension];
        let trackValue = ajaxJson[relevantTrackProperty] ?
            ajaxJson[relevantTrackProperty] : this.getTrackValue(relevantTrackProperty);

        let dimensionValueToBeRelativeTo = this.getDimensionValueToBeRelativeTo(template, dimension);

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
});