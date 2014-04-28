define(function() {
    var ElementAbstract = function()
    {
        var height;
        var width;
        var x;
        var y;
        var backgroundColour;
        var borderWidth;
        var borderColour;

        var editable = true;

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
    };

    return ElementAbstract;
});