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
    },
    "PRODUCT_DETAILS_CHANGE_FAILURE": function(state, action) {
        let {error, detail} = action.payload;
        n.showErrorNotification(error, "There was an error when attempting to update the " + detail + ".");
        return state;
    },
    "PRODUCT_DETAILS_CHANGE": function(state, action) {
        let {detail} = action.payload;
        n.success('Successfully updated ' + detail + '.');
        return state;
    }
});

export default dimensionReducer;

function applyDimensionsToState(state, dimensions) {
    return Object.assign({}, state, dimensions);
}