var TagBulkAction = function(event) {
    var error = function(error) {
        alert(error);
    }

    var orders = $("#datatable").cgDataTable("selected", ".order-id");
    if (!orders.length) {
        return;
    }

    var tag = $(this).data("tag") || $.trim(window.prompt("Name of Tag:", "tag"));
    if (!tag.length) {
        return;
    }

    $("#datatable_processing").css("visibility", "visible");
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
            $("#datatable").cgDataTable("redraw");
        }
    });
};