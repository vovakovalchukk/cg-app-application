define([
], function(
) {
    function BulkActionService() {
    }

    BulkActionService.BULK_ACTION_CONTAINER_SELECTOR = '.bulk-actions-holder';


    BulkActionService.prototype.refresh = function (bulkActions) {
        console.log(bulkActions);
        $(BulkActionService.BULK_ACTION_CONTAINER_SELECTOR).html(bulkActions);
    };

    return new BulkActionService;
});