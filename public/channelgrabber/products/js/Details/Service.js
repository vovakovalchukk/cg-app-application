define(['Details/DomListener'], function(DomListener) {
    function Service()
    {
        var domListener = new DomListener(this);
        this.getDomListener = function()
        {
            return domListener;
        };
    }

    Service.prototype.updateDetail = function(id, detail, value, sku)
    {
        console.log(id, detail, value, sku);
    };

    return Service;
});
