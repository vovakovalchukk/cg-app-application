define([
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/PubSubAbstract',
    'InvoiceDesigner/IdGenerator'
], function(
    EntityHydrateAbstract,
    PubSubAbstract,
    idGenerator
) {
    var ElementAbstract = function(additionalData)
    {
        EntityHydrateAbstract.call(this);
        PubSubAbstract.call(this);

        var data = {
            id: undefined,
            type: undefined,
            height: 50,
            width: 100,
            x: 0,
            y: 0,
            backgroundColour: undefined,
            borderWidth: 1,
            borderColour: 'black'
        };
        var baseInspectableAttributes = [];
        for (var field in data) {
            baseInspectableAttributes.push(field);
        }
        var extraInspectableAttributes = [];
        if (additionalData) {
            for (var field in additionalData) {
                data[field] = additionalData[field];
                extraInspectableAttributes.push(field);
            }
        }

        var editable = true;

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

        this.getType = function()
        {
            return this.get('type');
        };

        this.setType = function(newType)
        {
            this.set('type', newType);
            return this;
        };

        this.getHeight = function()
        {
            return this.get('height');
        };

        this.setHeight = function(newHeight, populating)
        {
            this.set('height', Number(newHeight).roundToNearest(0.5), populating);
            return this;
        };

        this.getWidth = function()
        {
            return this.get('width');
        };

        this.setWidth = function(newWidth, populating)
        {
            this.set('width', Number(newWidth).roundToNearest(0.5), populating);
            return this;
        };

        this.getX = function()
        {
            return this.get('x');
        };

        this.setX = function(newX, populating)
        {
            this.set('x', Number(newX).roundToNearest(0.5), populating);
            return this;
        };

        this.getY = function()
        {
            return this.get('y');
        };

        this.setY = function(newY, populating)
        {
            this.set('y', Number(newY).roundToNearest(0.5), populating);
            return this;
        };

        this.getBackgroundColour = function()
        {
            return this.get('backgroundColour');
        };

        this.setBackgroundColour = function(newBackgroundColour)
        {
            this.set('backgroundColour', newBackgroundColour);
            return this;
        };

        this.getBorderWidth = function()
        {
            return this.get('borderWidth');
        };

        this.setBorderWidth = function(newBorderWidth)
        {
            this.set('borderWidth', newBorderWidth);
            return this;
        };

        this.getBorderColour = function()
        {
            var borderColour = this.get('borderColour');
            return borderColour;
        };

        this.setBorderColour = function(newBorderColour)
        {
            this.set('borderColour', newBorderColour);
            return this;
        };

        this.get = function(field)
        {
            return data[field];
        };

        this.set = function(field, value, populating)
        {
            var oldValue = data[field];
            data[field] = value;

            if (oldValue === value || populating) {
                return;
            }
            this.publish();
        };

        this.getData = function()
        {
            return data;
        };

        this.isEditable = function()
        {
            return editable;
        };

        this.setEditable = function(newEditable)
        {
            editable = newEditable;
            return this;
        };

        this.getBaseInspectableAttributes = function()
        {
            return baseInspectableAttributes;
        };

        /**
         * Sub-classes can override this to provide extra inspectable attributes for themselves
         */
        this.getExtraInspectableAttributes = function()
        {
            return extraInspectableAttributes;
        };
    };

    var combinedPrototype = EntityHydrateAbstract.prototype;
    for (var key in PubSubAbstract.prototype) {
        combinedPrototype[key] = PubSubAbstract.prototype[key];
    }
    ElementAbstract.prototype = Object.create(combinedPrototype);

    ElementAbstract.prototype.getInspectableAttributes = function()
    {
        var baseAttribs = this.getBaseInspectableAttributes();
        var extraAttribs = this.getExtraInspectableAttributes();
        var allAttribs = baseAttribs;
        for (var key in extraAttribs) {
            allAttribs.push(extraAttribs[key]);
        }
        return allAttribs;
    };

    ElementAbstract.prototype.toJson = function()
    {
        var json = JSON.parse(JSON.stringify(this.getData()));
        json.x = json.x.mmToPt();
        json.y = json.y.mmToPt();
        json.height = json.height.mmToPt();
        json.width = json.width.mmToPt();
        json.borderWidth = (json.borderWidth ? json.borderWidth.mmToPt() : json.borderWidth);
        return json;
    };

    ElementAbstract.prototype.hydrate = function(data, populating)
    {
        EntityHydrateAbstract.prototype.hydrate.call(this, data, populating);
        this.setHeight(data.height, populating);
        this.setWidth(data.width, populating);
        this.setX(data.x, populating);
        this.setY(data.y, populating);
    };

    return ElementAbstract;
});