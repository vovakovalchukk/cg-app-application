define(function() {
    var columnPicker = function(jqElement)
    {
        this.jqElement = jqElement;
    };

    columnPicker.prototype.toggle = function()
    {
        this.jqElement.toggle();
    };

    return columnPicker;
});