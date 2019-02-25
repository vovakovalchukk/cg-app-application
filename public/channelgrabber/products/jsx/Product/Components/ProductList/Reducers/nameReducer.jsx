import reducerCreator from 'Common/Reducers/creator';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import utility from 'Product/Components/ProductList/utility';

"use strict";

let initialState = {
    names: {
        byProductId: {},
        allIds: []
    },
    focusedId: null
};
let nameReducer = reducerCreator(initialState, {
    "NAMES_FROM_PRODUCTS_EXTRACT": function(state,action){
        let {products} = action.payload;
        let stateCopy = Object.assign({}, state);
        stateCopy = getNamesOntoStateFromProducts(stateCopy, products);
        return stateCopy;
    },
    "NAME_CHANGE": function(state, action) {
        let {newName, productId} = action.payload;
        let stateCopy = Object.assign({}, state);
        stateCopy = setNameValuesToState(stateCopy, newName, productId);
        return stateCopy;
    },
    "NAME_FOCUS": function(state, action) {
        let {productId} = action.payload;
        let stateCopy = Object.assign({}, state);
        stateCopy = setNameFocus(stateCopy, productId);
        return stateCopy;
    },
    "NAME_BLUR": function(state, action) {
        let {productId} = action.payload;
        let stateCopy = Object.assign({}, state);
        stateCopy = setBlur(stateCopy, productId);
        return stateCopy;
    }
});

export default nameReducer;


function getNamesOntoStateFromProducts(state, products){
    state = Object.assign({}, state);
    for (let product of products){
        if(stateUtility.isVariation(product)){
          continue;
        }

        //todo - remove this hack
        let name = product.name + product.name + product.name;

        state.names.byProductId[product.id] = {
            originalValue: name,
            value : name,
            shortenedValue : utility.shortenNameForCell(name)
        };
        state.names.allIds.push(product.id);
    }
    return state;
}
function setNameValuesToState(stateCopy, newName, productId) {
    stateCopy.names.byProductId[productId].value = newName;
    stateCopy.names.byProductId[productId].shortenedValue = utility.shortenNameForCell(name)
    stateCopy.names.allIds.push(productId);
    return stateCopy;
}
function setNameFocus(state, productId) {
    state.focusedId = productId;
    return state;
}
function setBlur(state, productId) {
    if (state.focusedId === productId) {
        state.focusedId = null;
    }
    return state;
}