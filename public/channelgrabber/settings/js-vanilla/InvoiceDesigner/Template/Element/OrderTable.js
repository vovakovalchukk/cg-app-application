define(['InvoiceDesigner/Template/ElementAbstract'], function(ElementAbstract)
{
    var OrderTable = function()
    {
        var elementWidth = 700; // px
        var minHeight = 200; // px

        var additionalData = {
            showVat: false,
            linkedProductsDisplay: null
        };

        ElementAbstract.call(this, additionalData);

        this.set('type', 'OrderTable', true);
        this.setWidth(elementWidth.pxToMm())
            .setHeight(minHeight.pxToMm())
            .setMinWidth(elementWidth)
            .setMaxWidth(elementWidth)
            .setMinHeight(minHeight);

        this.getShowVat = function()
        {
            return this.get('showVat');
        };

        this.setShowVat = function(newShowVat)
        {
            this.set('showVat', !! newShowVat);
            return this;
        };
    };

    OrderTable.prototype = Object.create(ElementAbstract.prototype);

    return OrderTable;
});