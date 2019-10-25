define([
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/EntityAbstract',
    'InvoiceDesigner/PubSubAbstract',
    'Common/IdGenerator',
    'InvoiceDesigner/utility'
], function(
    EntityHydrateAbstract,
    EntityAbstract,
    PubSubAbstract,
    idGenerator,
    utility
) {
    var Entity = function()
    {
        EntityHydrateAbstract.call(this);
        EntityAbstract.call(this);
        PubSubAbstract.call(this);

        var contents;
        var data = {
            id: undefined,
            height: undefined,
            width: undefined,
            paperType: undefined,
            measurementUnit: 'mm'
        };

        this.getEntityName = function() {
            return 'PaperPage';
        };

        this.getId = function()
        {
            if (!this.get('id')) {
                this.set('id', idGenerator.generate(), true);
            }
            return this.get('id');
        };

        this.setId = function(newId)
        {
            this.set('id', newId);
            return this;
        };

        this.getHeight = function()
        {
            return this.get('height');
        };

        this.setHeight = function(template, newHeight, populating)
        {
            this.set("height", parseFloat(newHeight), populating, [{
                topicName: this.getTopicNames().paperSpace,
                template,
                dimensionAffected: "height",
                populating: false
            }]);
            return this;
        };

        this.getWidth = function()
        {
            return this.get('width');
        };

        this.setWidth = function(template, newWidth, populating)
        {
            this.set('width', parseFloat(newWidth), populating, [{
                topicName: this.getTopicNames().paperSpace,
                template,
                dimensionAffected: "width",
                populating: false
            }]);
            return this;
        };

        this.getPaperType = function()
        {
            return this.get('paperType');
        };

        this.setPaperType = function(newPaperType)
        {
            this.set('paperType', newPaperType);
            return this;
        };

        this.getMeasurementUnit = function()
        {
            return this.get('measurementUnit');
        };

        this.setMeasurementUnit = function(value, populating)
        {
            this.set('measurementUnit', value, populating);
            return this;
        };

        this.getData = function()
        {
            return data;
        };

        this.getType = function()
        {
            return 'paperpage';
        };

        /**
         * Used to store the generated HTML contents during rendering
         */
        this.htmlContents = function(htmlContents)
        {
            contents = htmlContents;
            return this;
        };

        this.getHtmlContents = function()
        {
            return contents;
        };
    };

    Entity.prototype = Object.create(utility.createPrototype([
        EntityHydrateAbstract,
        PubSubAbstract,
        EntityAbstract
    ]));

    Entity.prototype.toJson = function()
    {
        var json = JSON.parse(JSON.stringify(this.getData()));
        json.height = Number(json.height);
        json.width = Number(json.width);
        return json;
    };

    return Entity;
});