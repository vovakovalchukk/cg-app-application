import reducerCreator from 'Common/Reducers/creator';

let initialState = {
    stockModeOptions: [],
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
        let dimensions = Object.assign({}, state);

        let {
            productId,
            detail,
            newValue,
            currentDetails
        } = action.payload;

        console.log('DIMENSION_VALUE_CHANGE ', {
            productId,
            detail,
            newValue,
            currentDetails,
            dimensions
        });

        let dimensionExists = !!dimensions[detail].byProductId[productId];
        console.log('dimensionExists: ', dimensionExists);

//        if (dimensionExists) {
//            dimensions[detail].byProductId.value = dimensions[detail].byProductId ? dimensions[detail].byProductId.value : currentDetails[detail];
//            dimensions[detail].byProductId.active = !dimensions[detail].byProductId.active;
//            return applyDimensionToState(stateCopy, dimensions)
//        }

        if (!dimensions[detail].byProductId[productId]) {
            dimensions[detail].byProductId[productId] = {}
        }
        let productDimensions = dimensions[detail].byProductId[productId];
        productDimensions.value = currentDetails[detail];
        productDimensions.valueEdited = newValue;
        productDimensions.active = productDimensions.value !== productDimensions.valueEdited;

        console.log('productDimensions: ', productDimensions);
        console.log('dimensions[detail].byProductId[productId]: ', dimensions[detail].byProductId[productId]);
        
        
        return applyDimensionsToState(state, dimensions)
    }
});

export default dimensionReducer;

function applyDimensionsToState(state, dimensions) {
    return Object.assign({}, state, {
        dimensions
    });
}