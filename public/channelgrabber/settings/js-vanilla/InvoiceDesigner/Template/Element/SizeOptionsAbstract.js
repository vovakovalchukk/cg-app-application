define([], function()
{
    function SizeOptionsAbstract()
    {

    }

    SizeOptionsAbstract.prototype.getSizeOptions = function()
    {
        return this.get('sizeOptions');
    };

    SizeOptionsAbstract.prototype.setSizeOptions = function(newSizeOptions)
    {
        this.set('sizeOptions', newSizeOptions);
        return this;
    };

    SizeOptionsAbstract.prototype.getSizeOption = function()
    {
        return this.get('sizeOption');
    };

    SizeOptionsAbstract.prototype.setSizeOption = function(newSizeOption)
    {
        this.set('sizeOption', parseInt(newSizeOption));
        return this;
    };

    SizeOptionsAbstract.prototype.getDimensionsForSizeOption = function(sizeOption)
    {
        var index = parseInt(sizeOption) - 1;
        var sizeOptions = this.getSizeOptions();
        return sizeOptions[index];
    };

    SizeOptionsAbstract.prototype.getSizeOptionFromCurrentDimensions = function()
    {
        var option = 1;
        var width = this.getWidth();
        var sizeOptions = this.getSizeOptions();

        for (var index in sizeOptions) {
            // Avoid rounding issues by checking to nearest mm
            if (Math.floor(sizeOptions[index].width) == Math.floor(width)) {
                option = parseInt(index) + 1;
                break;
            }
        }

        return option;
    };

    return SizeOptionsAbstract;
});