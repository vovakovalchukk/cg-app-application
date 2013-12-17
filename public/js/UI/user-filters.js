$(function() {
    $("#userFilterForm").submit(function(event) {
        event.preventDefault();
        $("#users-table table").dataTable().fnDraw();
    });

    $("#userFilterForm a").click(function(event) {
        event.preventDefault();
        var form = $(this).closest("form")[0].reset();
        $("#users-table table").dataTable().fnDraw();
    });
});