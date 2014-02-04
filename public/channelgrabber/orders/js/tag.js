var TagBulkAction = function(url, orders, tag) {
    var url = url;
    var orders = orders || [];
    var tag = tag || "";

    this.action = function(event) {
        if (!orders.length) {
            return;
        }

        if (!tag.length) {
            return;
        }

        $("#datatable_processing").css("visibility", "visible");
        $.ajax({
            'url': this.url,
            'type': "POST",
            'data': {
                'tag': this.tag,
                'orders': this.orders
            },
            'complete': function() {
                $("#datatable").cgDataTable("redraw");
            }
        });
    };
};