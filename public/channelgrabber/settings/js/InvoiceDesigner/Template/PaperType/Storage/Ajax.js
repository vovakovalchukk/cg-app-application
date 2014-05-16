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
                backgroundImage: "http://www.scottiescardsandcrafts.co.uk/images/products/buzzcraft---pink-flowers-backing-sheet-Swrl.jpg",
                backgroundImageInverse: "http://www.scottiescardsandcrafts.co.uk/images/products/buzzcraft---pink-flowers-backing-sheet-Swrl.jpg"
            },
            {
                id: 2,
                name: "Single Label Top",
                height: "297",
                width: "210",
                backgroundImage: "http://www.scottiescardsandcrafts.co.uk/images/products/buzzcraft---pink-flowers-backing-sheet-Swrl.jpg",
                backgroundImageInverse: "http://www.scottiescardsandcrafts.co.uk/images/products/buzzcraft---pink-flowers-backing-sheet-Swrl.jpg"
            },
            {
                id: 3,
                name: "Single Label Bottom",
                height: "297",
                width: "210",
                backgroundImage: "http://www.scottiescardsandcrafts.co.uk/images/products/buzzcraft---pink-flowers-backing-sheet-Swrl.jpg",
                backgroundImageInverse: "http://www.scottiescardsandcrafts.co.uk/images/products/buzzcraft---pink-flowers-backing-sheet-Swrl.jpg"
            },
            {
                id: 4,
                name: "Double Label Top",
                height: "297",
                width: "210",
                backgroundImage: "http://www.scottiescardsandcrafts.co.uk/images/products/buzzcraft---pink-flowers-backing-sheet-Swrl.jpg",
                backgroundImageInverse: "http://www.scottiescardsandcrafts.co.uk/images/products/buzzcraft---pink-flowers-backing-sheet-Swrl.jpg"
            },
            {
                id: 5,
                name: "Double Label Bottom",
                height: "297",
                width: "210",
                backgroundImage: "http://www.scottiescardsandcrafts.co.uk/images/products/buzzcraft---pink-flowers-backing-sheet-Swrl.jpg",
                backgroundImageInverse: "http://www.scottiescardsandcrafts.co.uk/images/products/buzzcraft---pink-flowers-backing-sheet-Swrl.jpg"
            }
        ];

        return this.getMapper().fromArray(data);
    };

    return new Ajax();
});