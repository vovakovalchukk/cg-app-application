define([], function() {
    var ActionCreators = {
        cellChangeRecord: function(variationId, fieldId) {
            return {
                type: 'CELL_CHANGE_RECORD',
                payload: {
                    variationId: variationId,
                    fieldId: fieldId
                }
            }
        }
    };

    return ActionCreators;
});