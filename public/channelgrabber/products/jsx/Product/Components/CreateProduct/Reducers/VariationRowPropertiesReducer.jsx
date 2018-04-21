define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";
    var initialState = {
        variations: [
            {id: 0, hasChanged: false}
        ]
    };
    var VariationRowPropertiesReducer = reducerCreator(initialState, {
        "NEW_VARIATION_ROW_CREATE": function(state) {
            var variationsCopy = state.variations.slice();
            var newVariationId = (variationsCopy[state.variations.length-1].id)+1;
            variationsCopy.push({
                id: newVariationId,
                hasChanged:false
            });
            var newState  = Object.assign({},state,{
                variations: variationsCopy
            })
            return newState;
        }
    });
    return VariationRowPropertiesReducer;
});