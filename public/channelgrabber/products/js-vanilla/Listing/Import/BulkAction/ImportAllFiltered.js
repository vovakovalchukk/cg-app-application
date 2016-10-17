define([
    'BulkActionAbstract',
    'Listing/Import/Service'
], function(
    BulkActionAbstract,
    service
) {
    var ImportAllFiltered = function()
    {
        BulkActionAbstract.call(this);

        this.getService = function()
        {
            return service;
        };
    };

    ImportAllFiltered.prototype = Object.create(BulkActionAbstract.prototype);

    ImportAllFiltered.URL = '/products/listing/import/importAllFiltered';
    ImportAllFiltered.MESSAGE_SUCCESS = 'Listings import started successfully';
    ImportAllFiltered.MESSAGE_PENDING = 'Importing all listings that match the filters';

    ImportAllFiltered.prototype.invoke = function()
    {
        var filters = this.getFilters();

        this.getNotificationHandler().notice(ImportAllFiltered.MESSAGE_PENDING);
        var data = filters;
        this.sendAjaxRequest(
            ImportAllFiltered.URL,
            data,
            this.handleSuccess,
            null,
            this
        );
    };

    ImportAllFiltered.prototype.getFilters = function()
    {
        var filters = [];
        $("#filters :input[name]").each(function() {
            var value = $.trim($(this).val());
            if (!value.length) {
                return;
            }
            var name = $(this).attr("name").replace(/^(.*?)(\[.*\])?$/g, "filter[$1]$2");

            filters.push({
                "name": name,
                "value": value
            });
        });
        return filters;
    };

    ImportAllFiltered.prototype.handleSuccess = function()
    {
        this.getNotificationHandler().success(ImportAllFiltered.MESSAGE_SUCCESS);
        this.getService().refreshDatatable();
    };

    return new ImportAllFiltered();
});