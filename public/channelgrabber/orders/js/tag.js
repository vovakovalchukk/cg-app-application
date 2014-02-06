define(function() {
    return function(event) {
        var error = function(error) {
            alert(error);
        }

        var datatable = $(this).data("datatable");
        var orders = $(this).data("orders");

        if (!orders && datatable) {
            orders = $("#" + datatable).cgDataTable("selected", ".order-id");
        }

        if (!orders.length) {
            return;
        }

        var tag = $(this).data("tag") || $.trim(window.prompt("Name of Tag:", "tag"));
        if (!tag.length) {
            return;
        }

        if (datatable) {
            $("#" + datatable + "_processing").css("visibility", "visible");
        }

        $.ajax({
            url: $(this).data("url"),
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
                if (datatable) {
                    $("#" + datatable).cgDataTable("redraw");
                }
            }
        });
    };
});