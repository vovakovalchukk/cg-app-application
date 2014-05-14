define(['InvoiceDesigner/Template/PaperType/StorageAbstract', 'jquery'], function(StorageAbstract, $)
{
    var Ajax = function()
    {
        StorageAbstract.call(this);
    };

    Ajax.prototype = Object.create(StorageAbstract.prototype);

    Ajax.prototype.fetchAll = function()
    {
        var data = [
            {
                id: 1,
                name: "No Label (Blank)",
                backgroundImage: "/path/to/no/label",
                backgroundImageInverse: "/path/to/no/label/inverse"
            },
            {
                id: 2,
                name: "Single Label Top",
                backgroundImage: "/path/to/single/label/top",
                backgroundImageInverse: "/path/to/single/label/top/inverse"
            },
            {
                id: 3,
                name: "Single Label Bottom",
                backgroundImage: "/path/to/single/label/bottom",
                backgroundImageInverse: "/path/to/single/label/bottom/inverse"
            },
            {
                id: 4,
                name: "Double Label Top",
                backgroundImage: "/path/to/double/label/top",
                backgroundImageInverse: "/path/to/double/label/top/inverse"
            },
            {
                id: 5,
                name: "Double Label Bottom",
                backgroundImage: "/path/to/double/label/bottom",
                backgroundImageInverse: "/path/to/double/label/bottom/inverse"
            }
        ];

        return this.getMapper().fromArray(data);
    };

    return new Ajax();
});