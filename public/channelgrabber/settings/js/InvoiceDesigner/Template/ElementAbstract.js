define(['InvoiceDesigner/PubSubAbstract'], function(PubSubAbstract) {
    var ElementAbstract = function()
    {
        PubSubAbstract.call(this);

        var id;
        var type;
        var height;
        var width;
        var x;
        var y;
        var backgroundColour;
        var borderWidth;
        var borderColour;

        var editable = true;

        var baseInspectableAttributes = [
            'height', 'width', 'x', 'y', 'backgroundColour', 'borderWidth', 'borderColour'
        ];

        this.getId = function()
        {
            if (!id) {
                this.setId(generateId());
            }
            return id;
        };

        this.setId = function(newId)
        {
            id = newId;
            return this;
        };

        this.getType = function()
        {
            return type;
        };

        this.setType = function(newType)
        {
            type = newType;
            return this;
        };

        this.getHeight = function()
        {
            return height;
        };

        this.setHeight = function(newHeight)
        {
            height = newHeight;
            return this;
        };

        this.getWidth = function()
        {
            return width;
        };

        this.setWidth = function(newWidth)
        {
            width = newWidth;
            return this;
        };

        this.getX = function()
        {
            return x;
        };

        this.setX = function(newX)
        {
            x = newX;
            return this;
        };

        this.getY = function()
        {
            return y;
        };

        this.setY = function(newY)
        {
            y = newY;
            return this;
        };

        this.getBackgroundColour = function()
        {
            return backgroundColour;
        };

        this.setBackgroundColour = function(newBackgroundColour)
        {
            backgroundColour = newBackgroundColour;
            return this;
        };

        this.getBorderWidth = function()
        {
            return borderWidth;
        };

        this.setBorderWidth = function(newBorderWidth)
        {
            borderWidth = newBorderWidth;
            return this;
        };

        this.getBorderColour = function()
        {
            return borderColour;
        };

        this.setBorderColour = function(newBorderColour)
        {
            borderColour = newBorderColour;
            return this;
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
            return [];
        };

        // Elements aren't expected to have IDs so generate one
        var generateId = function()
        {
            return (new Date()).getTime()+String(Math.random()).substr(2);
        };
    };

    ElementAbstract.prototype = Object.create(PubSubAbstract.prototype);

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