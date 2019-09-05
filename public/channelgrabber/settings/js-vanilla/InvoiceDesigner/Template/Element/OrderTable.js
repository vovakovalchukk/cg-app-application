define([
    'InvoiceDesigner/Template/ElementAbstract',
    'InvoiceDesigner/Template/Storage/Table'
], function(
    ElementAbstract,
    TableStorage
) {
    var OrderTable = function()
    {
        console.log('in OrderTable instatiation');
//
        const elementWidth = 700; // px
        const minHeight = 200; // px
        const tableColumns = TableStorage.getDefaultColumns();

        var additionalData = {
            showVat: false,
            linkedProductsDisplay: null,
            //todo - link this up with an inspector
            tableColumns
        };

        ElementAbstract.call(this, additionalData);

        //todo - might need to set tableColumns ere...
        this.set('type', 'OrderTable', true);
        this.setWidth(elementWidth.pxToMm())
            .setHeight(minHeight.pxToMm())
            .setMinWidth(elementWidth)
            .setMaxWidth(elementWidth)
            .setMinHeight(minHeight);


        this.getLinkedProductsDisplay = function()
        {
            return this.get('linkedProductsDisplay');
        };

        this.setLinkedProductsDisplay = function(newLinkedProductsDisplay)
        {
            this.set('linkedProductsDisplay', newLinkedProductsDisplay);
            return this;
        };

        this.getShowVat = function()
        {
            return this.get('showVat');
        };

        this.setShowVat = function(newShowVat)
        {
            this.set('showVat', !! newShowVat);
            return this;
        };

        this.getTableColumns = function() {
            return this.get('tableColumns');
        }
    };

    OrderTable.prototype = Object.create(ElementAbstract.prototype);

    return OrderTable;
});