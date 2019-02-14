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
    },
    weight: {
        byProductId: {}
    }
};

let detailReducer = reducerCreator(initialState, {
    "DETAIL_VALUE_CHANGE": function(state, action) {
        let {
            productId,
            detail,
            newValue,
            currentDetailsFromProductState
        } = action.payload;
        let details = Object.assign({}, state);

        let productDetails = details[detail].byProductId[productId];

        if (!productDetails) {
            productDetails = details[detail].byProductId[productId] = {
                value: currentDetailsFromProductState[detail]
            };
        }
        productDetails.valueEdited = newValue ? newValue : "";
        productDetails.isEditing = true;
        return applyDetailsToState(state, details)
    },
    "DETAIL_CANCEL_INPUT": function(state, action) {
        let {detail, row} = action.payload;
        let details = Object.assign({}, state);
        let variationDetails = details[detail].byProductId[row.id];
        variationDetails.valueEdited = variationDetails.value;
        variationDetails.isEditing = false;
        return applyDetailsToState(state, details);
    },
    "IS_EDITING_SET": function(state, action) {
        let {
            productId,
            detail,
            setToBoolean
        } = action.payload;
        let details = Object.assign({}, state);
        let productDetail = details[detail].byProductId[productId];
        if (!productDetail) {
            productDetail = {};
        }
        productDetail.isEditing = setToBoolean;
        return applyDetailsToState(state, details)
    },
    "PRODUCT_DETAILS_CHANGE_FAILURE": function(state, action) {
        let {error, detail} = action.payload;
        n.showErrorNotification(error, "There was an error when attempting to update the " + detail + ".");
        return state;
    },
    "PRODUCT_DETAILS_CHANGE_SUCCESS": function(state, action) {
        let {detail, row} = action.payload;
        n.success('Successfully updated ' + detail + '.');
        let details = Object.assign({}, state);
        let variationDetails = details[detail].byProductId[row.id];
        variationDetails.value = variationDetails.valueEdited ? variationDetails.valueEdited : "";
        variationDetails.isEditing = false;
        delete variationDetails.valueEdited;
        return applyDetailsToState(state, details)
    }
});

export default detailReducer;

function applyDetailsToState(state, details) {
    return Object.assign({}, state, details);
}