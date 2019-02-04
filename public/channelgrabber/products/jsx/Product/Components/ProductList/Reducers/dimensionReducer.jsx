import reducerCreator from 'Common/Reducers/creator';

let initialState = {
    height: {
        byProductId: {}
    },
    width: {
        byProductId: {}
    },
    length: {
        byProductId: {}
    }
};

let dimensionReducer = reducerCreator(initialState, {
    "DIMENSION_VALUE_CHANGE": function(state, action) {
        let {
            productId,
            detail,
            newValue,
            currentDetailsFromProductState
        } = action.payload;
        let dimensions = Object.assign({}, state);

        let productDimension = dimensions[detail].byProductId[productId];

        if (!productDimension) {
            productDimension = dimensions[detail].byProductId[productId] = {};
        }
        productDimension.value = currentDetailsFromProductState[detail];
        productDimension.valueEdited = newValue;
        productDimension.active = productDimension.value !== productDimension.valueEdited;

        return applyDimensionsToState(state, dimensions)
    },
    "IS_EDITING_SET": function(state,action){
        let {
           productId,
           detail,
           setToBoolean
        } = action.payload;
        let dimensions = Object.assign({}, state);
        let productDimension = dimensions[detail].byProductId[productId];
        if (!productDimension) {
            productDimension = {}
        }
        productDimension.isEditing = setToBoolean;
        return applyDimensionsToState(state, dimensions)
    }
});

export default dimensionReducer;

function applyDimensionsToState(state, dimensions) {
    return Object.assign({}, state, dimensions);
}