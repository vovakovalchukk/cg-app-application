define(['InvoiceDesigner/Template/PaperType/StorageAbstract', 'jquery'], function(StorageAbstract, $)
{
    var Ajax = function()
    {
        StorageAbstract.call(this);
    };

    Ajax.prototype = Object.create(StorageAbstract.prototype);

    Ajax.prototype.fetchAll = function()
    {
        let data = [
            {
                id: 1,
                name: "A4",
                height: "297",
                width: "210",
                backgroundImage: "",
                backgroundImageInverse: ""
            },
            {
                id: 2,
                name: "A5",
                height: "148",
                width: "210",
                backgroundImage: "",
                backgroundImageInverse: ""
            },
            {
                id: 3,
                name: "Letter",
                height: "279",
                width: "216",
                backgroundImage: "",
                backgroundImageInverse: ""
            },
            {
                id: 4,
                name: "Legal",
                height: "356",
                width: "216",
                backgroundImage: "",
                backgroundImageInverse: ""
            },
            {
                id:5,
                name: "6 x 4 Courier Label",
                height: "100",
                width: "150",
                backgroundImage: "",
                backgroundImageInverse: ""
            }
        ];

        return this.getMapper().fromArray(data);
    };
    return new Ajax();
});