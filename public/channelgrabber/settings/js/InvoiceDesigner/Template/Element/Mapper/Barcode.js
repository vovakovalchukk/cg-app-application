define([
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/Element/Barcode'
], function(
    MapperAbstract,
    BarcodeElement
) {
    var Barcode = function()
    {
        MapperAbstract.call(this);
    };

    Barcode.prototype = Object.create(MapperAbstract.prototype);

    Barcode.prototype.getHtmlContents = function(element)
    {
        return '<div class="sprite-sprite sprite-barcode-167-black"></div>';
    };

    Barcode.prototype.createElement = function()
    {
        return new BarcodeElement();
    };

    return new Barcode();
});