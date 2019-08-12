define([
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/PubSubAbstract',
], function(
    templateService,
    EntityHydrateAbstract,
    PubSubAbstract
) {
    function getHeight(multiPageData,paperPageData){
        return '300px';
    }

    const TRACK_TO_DIMENSION = {
        rows: 'height',
        columns: 'width'
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

        this.getData = function(){
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

        this.createWorkableAreaIndicator = function(template){
            let multiPageData = this.getData();
            const paperPageData = template.getPaperPage().getData();
            console.log('in createWorkableArea...', data);
            let element = document.createElement('div');
            element.id = 'workableAreaIndicator';
            element.className = 'test';
            element.style.height = getHeight(multiPageData, paperPageData);
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

        this.setWorkableAreaIndicatorElement = function(newElement){
            workableAreaIndicatorElement = newElement;
        };

        this.get = function(field)
        {
            return data[field];
        };

        this.set = function(field, value, populating)
        {
            data[field] = value;

            if (populating) {
                return;
            }

            this.publish();
        };
    };

    let combinedPrototype = createPrototype();

    Entity.prototype = Object.create(combinedPrototype);

    Entity.prototype.getMaxDimensionFromTrackValue = function(template, track, value){
        const paperPage = template.getPaperPage();
        const dimensionProperty = TRACK_TO_DIMENSION[track];

        const paperPageData = paperPage.getData();

        let maxDimension = Math.floor(paperPageData[dimensionProperty] / value);


        console.log('in getMaxDimension...');

        // todo - need to get the relvant property from paperPage and divide by dimension
    };

    Entity.prototype.toJson = function(){
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