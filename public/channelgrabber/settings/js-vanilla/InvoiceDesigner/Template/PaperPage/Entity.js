define([
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/PubSubAbstract',
    'Common/IdGenerator'
], function(
    EntityHydrateAbstract,
    PubSubAbstract,
    idGenerator
) {
    var Entity = function()
    {
        EntityHydrateAbstract.call(this);
        PubSubAbstract.call(this);

        var contents;
        var data = {
            id: undefined,
            height: undefined,
            width: undefined,
            paperType: undefined,
            backgroundImage: undefined,
            inverse: undefined
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

        this.getInverse = function()
        {
            return this.get('inverse');
        };

        this.setInverse = function(inverse)
        {
            this.set('inverse', inverse);
            return this;
        };

        this.getHeight = function()
        {
            return this.get('height');
        };

        this.setHeight = function(newHeight)
        {
            this.set('height', parseFloat(newHeight));
            return this;
        };

        this.getWidth = function()
        {
            return this.get('width');
        };

        this.setWidth = function(newWidth)
        {
            this.set('width', parseFloat(newWidth));
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

        this.getBackgroundImage = function()
        {
            return this.get('backgroundImage');
        };

        this.setBackgroundImage = function(newBackgroundImage)
        {
            this.set('backgroundImage', newBackgroundImage);
            return this;
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

    var combinedPrototype = EntityHydrateAbstract.prototype;
    for (var key in PubSubAbstract.prototype) {
        combinedPrototype[key] = PubSubAbstract.prototype[key];
    }
    Entity.prototype = Object.create(combinedPrototype);

    Entity.prototype.toJson = function()
    {
        var json = JSON.parse(JSON.stringify(this.getData()));
        json.height = Number(json.height).mmToPt();
        json.width = Number(json.width).mmToPt(); 
        return json;
    };

    return Entity;
});