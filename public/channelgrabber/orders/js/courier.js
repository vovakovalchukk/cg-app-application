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

        var inputsHtml = '';
        for (var count in orders) {
            inputsHtml += '<input name="order[]" value="'+orders[count]+'" type="hidden" />';
        }
        // TODO: pjax
        $('<form method="POST" action="'+url+'" target="_top">'+inputsHtml+'</form>').appendTo('body').submit().remove();
    };

    return Courier;
});