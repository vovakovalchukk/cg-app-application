define([], function()
{
    function Courier()
    {
    }

    Courier.prototype.action = function(element)
    {
        var datatable = $(element).data("datatable");
        var url = $(element).data("url");
        var orders = $(element).data("orders");
        if (!orders && datatable) {
            orders = $("#" + datatable).cgDataTable("selected", ".checkbox-id");
        }
        if (!orders || orders.length == 0) {
            return;
        }

        $('<a href="' + url + '" />').cgPjax('post', {"order": orders});
    };

    return Courier;
});