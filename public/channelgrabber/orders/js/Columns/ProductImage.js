define([], function()
{
    function ProductImage(tableElement)
    {
        this.getTableElement = function()
        {
            return tableElement;
        };

        var init = function()
        {
            this.listenForColumnToggle();
        };
        init.call(this);
    }

    ProductImage.prototype.listenForColumnToggle = function()
    {
        $(this.getTableElement()).on('fnSetColumnVis', function(event, columnIndex, on)
        {
            
        });
    };

    return ProductImage;
});