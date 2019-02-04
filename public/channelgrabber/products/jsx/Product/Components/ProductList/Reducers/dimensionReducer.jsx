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
        productDimension.value = productDimension.value === undefined ? currentDetailsFromProductState[detail] : productDimension.value;
        productDimension.valueEdited = newValue ? newValue : "";
        return applyDimensionsToState(state, dimensions)
    },
    "DIMENSION_CANCEL_INPUT": function(state, action) {
        let {detail, variation} = action.payload;
        let dimensions = Object.assign({}, state);
        let variationDimension = dimensions[detail].byProductId[variation.id];
        variationDimension.valueEdited = variationDimension.value;
        variationDimension.isEditing = false;
        return applyDimensionsToState(state, dimensions);
    },
    "IS_EDITING_SET": function(state, action) {
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
    "PRODUCT_DETAILS_CHANGE_SUCCESS": function(state, action) {
        let {detail, row} = action.payload;
        n.success('Successfully updated ' + detail + '.');
        let dimensions = Object.assign({}, state);
        let variationDimension = dimensions[detail].byProductId[row.id];
        variationDimension.value = variationDimension.valueEdited;
        variationDimension.isEditing = false;
        delete variationDimension.valueEdited;
        debugger;
        return applyDimensionsToState(state, dimensions)
    }
});

export default dimensionReducer;

function applyDimensionsToState(state, dimensions) {
    return Object.assign({}, state, dimensions);
}