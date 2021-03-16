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

        // Can't save the filter as part of the main call as we're opening an iframe so can't get the ID back
        this.saveFilterOnly();

        var postData = this.getDataToSubmit();
        if (orders.length) {
            postData['referrer'] = window.location.pathname;
            $('<a href="' + url + '" />').cgPjax('post', postData);
        }
    };

    return Courier;
});