var TagBulkAction = function(url, tag, orders) {
    var url = url;
    var tag = tag;
    var orders = orders;

    this.action = function(event) {
        if (!tag.length) {
            return;
        }

        if (!orders.length) {
            return;
        }

        $("#datatable_processing").css("visibility", "visible");
        $.ajax({
            context: this,
            url: url,
            type: "POST",
            dataType: 'json',
            data: {
                'orders': orders,
                'tag': tag
            },
            success : function(data) {
                if (data.tagged || !data.error) {
                    return
                }
                error(data.error);
            },
            error: function() {
                error("Network Error");
            },
            complete: function() {
                $("#datatable").cgDataTable("redraw");
            }
        });
    };

    var error = function(error) {
        alert(error);
    }
};