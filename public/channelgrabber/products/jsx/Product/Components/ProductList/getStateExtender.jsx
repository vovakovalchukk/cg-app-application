define([
    'Product/Components/ProductList/stateGetters'
], function(
    stateGetters
) {
    "use strict";
    
    let getStateEnhancer = getState => {
        const _originalGetState = getState;
        let newGetState = Object.assign(_originalGetState, {});
        newGetState.customGetters = stateGetters(_originalGetState);
        return newGetState;
    };
    
    return getStateEnhancer;
});