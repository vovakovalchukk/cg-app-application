import reducerCreator from 'Common/Reducers/creator';

"use strict";

let initialState = {
    activeSelect: {
        columnKey: '',
        productId: ''
    }
};

let selectReducer = reducerCreator(initialState, {
    "REMOVE_ACTIVE_SELECT": function(state, action){
        let stateToReturn = Object.assign({}, state);
        return resetSelectActive(stateToReturn);
    },
    "SELECT_ACTIVE_TOGGLE": function(state, action) {
        let {productId, columnKey, index} = action.payload;
        let stateToReturn = Object.assign({}, state);

        if(isPreviousActiveSelect(stateToReturn, productId, columnKey, index)){
            stateToReturn = resetSelectActive(stateToReturn);
            return stateToReturn;
        }

        stateToReturn = setNewSelectAsActive(stateToReturn, productId, columnKey, index);

        return stateToReturn
    }
});

export default selectReducer;

function isPreviousActiveSelect(state, productId, columnKey, index){
    if((typeof state.activeSelect.index === 'number' && typeof index == 'number')
        && (state.activeSelect.index !== index)){
        return false;
    }
    return state.activeSelect.productId === productId && state.activeSelect.columnKey === columnKey;
}

function resetSelectActive(state) {
    state.activeSelect = initialState.activeSelect;
    return state;
}

function setNewSelectAsActive(state, productId, columnKey, index){
    state.activeSelect = {
        columnKey,
        productId,
        index
    };
    return state;
}