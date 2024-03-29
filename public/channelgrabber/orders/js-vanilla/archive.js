define(['Orders/OrdersBulkActionAbstract'], function(OrdersBulkActionAbstract)
{
    function Archive()
    {
        OrdersBulkActionAbstract.call(this);
    }

    Archive.prototype = Object.create(OrdersBulkActionAbstract.prototype);

    Archive.prototype.invoke = function()
    {
        var datatable = this.getDataTableElement();
        var orders = this.getOrders();
        if (!datatable.length || !orders.length) {
            return;
        }

        var ajax = {
            url: this.getElement().data("url"),
            type: "POST",
            dataType: 'json',
            data: this.getDataToSubmit(),
            context: this,
            success : function(data) {
                if (data.archived) {
                    this.setFilterId(data.filterId);
                    return this.getNotificationHandler().success(this.getElement().data("success"));
                } else if (!data.error) {
                    return this.getNotificationHandler().error(this.getElement().data("error"));
                }
                this.getNotificationHandler().error(data.error);
            },
            error: function (error, textStatus, errorThrown) {
                return this.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
            },
            complete: function() {
                datatable.cgDataTable("redraw");
            }
        };

        this.getNotificationHandler().notice(this.getElement().data("message"));
        $.ajax(ajax);
    };

    return Archive;
});
