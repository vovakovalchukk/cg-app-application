import reducerCreator from 'Common/Reducers/creator';
import stateUtility from 'Product/Components/ProductList/stateUtility';

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
            value : name,
            shortenedValue : shortenName(name)
        };
        state.names.allIds.push(product.id);
    }
    return state;
}
function shortenName(name) {
    let cutOffLength = 60;
    if(name.length > cutOffLength){
        return name.substring(0, cutOffLength) + "...";
    }
    return name;
}
function setNameValuesToState(stateCopy, newName, productId) {
    stateCopy = initProductObj(stateCopy, productId);
    stateCopy.names.byProductId[productId].value = newName;
    stateCopy.names.byProductId[productId].shortenedValue = shortenName(newName);
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
function initProductObj(state, productId) {
    if (!state.names.byProductId[productId]) {
        state.names.byProductId[productId] = {}
    }
    return state;
}
