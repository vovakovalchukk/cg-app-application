define([], function() {
    function BulkActionService() {
    }

    BulkActionService.BULK_ACTION_CONTAINER_SELECTOR = '.bulk-actions-holder.order-detail > #bulk-actions';

    BulkActionService.prototype.refresh = function(bulkActions) {
        $(bulkActions).find('.bulk-action').each(function(index, element) {
            var isDisabled = $(element).hasClass('disabled');
            var existingBulkActions = $(BulkActionService.BULK_ACTION_CONTAINER_SELECTOR).find('.bulk-action');
            $(existingBulkActions[index]).toggleClass('disabled', isDisabled);
        });
    };

    BulkActionService.prototype.getSelectedOrders = function() {
        return $('#datatable').cgDataTable("selected", ".checkbox-id");
    };

    return new BulkActionService;
});