define(['InvoiceDesigner/Template/PaperType/StorageAbstract', 'jQuery'], function(StorageAbstract, $)
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
                backgroundImage: "/path/to/no/label"
            },
            {
                id: 2,
                name: "Single Label Top",
                backgroundImage: "/path/to/single/label/top"
            },
            {
                id: 3,
                name: "Single Label Bottom",
                backgroundImage: "/path/to/single/label/bottom"
            },
            {
                id: 4,
                name: "Double Label Top",
                backgroundImage: "/path/to/double/label/top"
            },
            {
                id: 5,
                name: "Double Label Bottom",
                backgroundImage: "/path/to/double/label/bottom"
            }
        ];

        return this.getMapper().fromArray(data);
    };

    return new Ajax();
});