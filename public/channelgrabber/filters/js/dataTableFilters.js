$(document).ready(function()
{
    var datatable = $("#datatable");
    datatable.on('before-cgdatatable-init', function()
    {
        datatable.on("redraw", function() {
            datatable.removeData("filterId");
        });

        datatable.on("fnServerData", function(event, sSource, aoData, fnCallback, oSettings) {
            var filterId = datatable.data("filterId");
            if (filterId) {
                return;
            }

            $("#filters :input[name]").each(function() {
                var value = $.trim($(this).val());
                if (!value.length) {
                    return;
                }
                var name = $(this).attr("name").replace(/^(.*?)(\[.*\])?$/g, "filter[$1]$2");

                aoData.push({
                    "name": name,
                    "value": value
                });
            });

        });

        datatable.on("jqXHRBeforeSend", function(event, request, settings) {
            var filterId = datatable.data("filterId");
            if (filterId) {
                settings.url += "/" + filterId;
            }

            request.done(function(data, status, request) {
                if (!data.sFilterId) {
                    return;
                }

                $(event.target).data("filterId", data.sFilterId);
            });
        });

        if ($().cgPjax) {
            datatable.on("fnRowCallback", function(event, nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $("a.title", nRow).cgPjax();
            });
        }
    });

    datatable.on('after-cgdatatable-init', function()
    {
        $('#datatable-select-all').on('change', function()
        {
            $("#filters").removeData("id");
        });

        $("#filters").on('apply', function()
        {
            $("#filters").removeData("id");
        });
    });
});