$(document).ready(function()
{
    $("#datatable").on("renderColumn", function(event, cgmustache, template, column, data) {
        data.formatDateTime = cgmustache.formatDateTime(data, column.mData);
    });
});