define([
], function(
) {
    function BulkActionService() {
    }

    BulkActionService.BULK_ACTION_CONTAINER_SELECTOR = '#bulk-actions';


    BulkActionService.prototype.refresh = function (bulkActions) {
        $(BulkActionService.BULK_ACTION_CONTAINER_SELECTOR).html(bulkActions);
    };

    return new BulkActionService;
});