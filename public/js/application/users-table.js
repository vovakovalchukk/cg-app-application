$(function() {
    $("#users-table table").dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": source,
        "sServerMethod": "POST",
        "fnServerParams": function(request) {
            var filters = {};
            $("#userFilterForm input:text").each(function() {
                request.push({
                    "name": $(this).attr("name"),
                    "value": $(this).val()
                });
            });
        },
        "sPaginationType": "full_numbers",
        "iDisplayLength": limit,
        "aoColumns": columns,
        "sDom": "<\"loader\"r><\"information\"i><\"pagination\"p>t<\"pagination\"p>"
    });

    $("#users-table table").on("click", ".action .delete", function(event) {
        event.preventDefault();
        
        var self = $(this);
        $.ajax({
            "url": self.attr("href"),
            "dataType": "json",
            "success": function(data) {
                if (!data.deleted) {
                    alert(data.exception);
                    return;
                }

                $("#users-table table").dataTable().fnDraw();
            }
        });
    });
});