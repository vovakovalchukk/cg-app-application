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
                height: "297",
                width: "210",
                backgroundImage: "",
                backgroundImageInverse: ""
            },
            {
                id: 2,
                name: "Forms Plus FPS-3",
                height: "297",
                width: "210",
                backgroundImage: "/channelgrabber/settings/img/InvoiceDesigner/Template/PaperType/style-c-label.png",
                backgroundImageInverse: "/channelgrabber/settings/img/InvoiceDesigner/Template/PaperType/style-c-label-inverse.png"
            }
        ];

        return this.getMapper().fromArray(data);
    };

    return new Ajax();
});