define(['Orders/OrdersBulkActionAbstract'], function(OrdersBulkActionAbstract)
{
    function Courier()
    {
        OrdersBulkActionAbstract.call(this);
    }

    Courier.prototype = Object.create(OrdersBulkActionAbstract.prototype);

    Courier.prototype.invoke = function()
    {
        var element = this.getElement();
        var url = element.data("url");
        var orders = this.getOrders();
        if (!orders) {
            return;
        }

        $('<a href="' + url + '" />').cgPjax('post', this.getDataToSubmit());
    };

    return Courier;
});