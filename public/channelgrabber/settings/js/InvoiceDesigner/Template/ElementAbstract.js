define([
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/PubSubAbstract'
], function(
    EntityHydrateAbstract,
    PubSubAbstract
) {
    var ElementAbstract = function(additionalData)
    {
        EntityHydrateAbstract.call(this);
        PubSubAbstract.call(this);

        var data = {
            id: undefined,
            type: undefined,
            height: undefined,
            width: undefined,
            x: undefined,
            y: undefined,
            backgroundColour: undefined,
            borderWidth: undefined,
            borderColour: undefined
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

        this.setHeight = function(newHeight)
        {
            this.set('height', newHeight);
            return this;
        };

        this.getWidth = function()
        {
            return this.get('width');
        };

        this.setWidth = function(newWidth)
        {
            this.set('width', newWidth);
            return this;
        };

        this.getX = function()
        {
            return this.get('x');
        };

        this.setX = function(newX)
        {
            this.set('x', newX);
            return this;
        };

        this.getY = function()
        {
            return this.get('y');
        };

        this.setY = function(newY)
        {
            this.set('y', newY);
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
            return this.get('borderColour');
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
            data[field] = value;

            if (populating) {
                return;
            }
            this.publish();
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
        this.getExtraInspectableAttributes = function() {
            return extraInspectableAttributes;
        };

        // Elements aren't expected to have IDs so generate one
        var generateId = function()
        {
            return  (new Date()).getTime()+String(Math.random()).substr(2);
        };
        this.setId(generateId());
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
        return {
            type: this.getType(),
            height: this.getHeight(),
            width: this.getWidth(),
            x: this.getX(),
            y: this.getY(),
            backgroundColour: this.getBackgroundColour(),
            borderWidth: this.getBorderWidth(),
            borderColour: this.getBorderColour()
        };
    };

    return ElementAbstract;
});