import reducerCreator from 'Common/Reducers/creator';

"use strict";

let initialState = {
    activeSelect: {
        columnKey: '',
        productId: ''
    }
};

let selectReducer = reducerCreator(initialState, {
    "SELECT_ACTIVE_TOGGLE": function(state, action) {
        let {productId, columnKey} = action.payload;
        let stateToReturn = Object.assign({}, state);

        if(isPreviousActiveSelect(stateToReturn, productId,columnKey)){
            stateToReturn = resetSelectActive(stateToReturn);
            return stateToReturn;
        }

        stateToReturn = setNewSelectAsActive(stateToReturn, productId, columnKey);

        return stateToReturn
    }
});

export default selectReducer;

function isPreviousActiveSelect(state, productId, columnKey){
    return state.activeSelect.productId === productId && state.activeSelect.columnKey === columnKey;
}

function resetSelectActive(state) {
    state.activeSelect = initialState.activeSelect;
    return state;
}

function setNewSelectAsActive(state, productId, columnKey){
    state.activeSelect = {
        columnKey,
        productId
    };
    return state;
}