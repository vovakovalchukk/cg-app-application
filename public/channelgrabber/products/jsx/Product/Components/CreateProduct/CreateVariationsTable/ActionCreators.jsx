define([], function() {

    var ActionCreators = {
        newVariationRowCreate: function() {
            return {
                type: 'NEW_VARIATION_ROW_CREATE'
            }
        },
        newAttributeColumnRequest: function(){
            return{
                type:'NEW_ATTRIBUTE_COLUMN_REQUEST'
            };
        },
        newVariationRowCreateRequest: function( variationId) {
            return function(dispatch, getState) {
                var currState = getState();
                if (!variationIsEmpty(currState,variationId)) {
                    dispatch(ActionCreators.newVariationRowCreate());
                }
            }
        }
    };

    return ActionCreators;

    function variationIsEmpty(currState,variationId){
        return currState.form.createProductForm.values && currState.form.createProductForm.values.variations["variation-" + variationId];
    }


});