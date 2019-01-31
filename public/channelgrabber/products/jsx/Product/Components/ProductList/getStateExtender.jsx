import stateGetters from 'Product/Components/ProductList/stateGetters';

let getStateEnhancer = getState => {
    const _originalGetState = getState;
    let newGetState = Object.assign(_originalGetState, {});
    newGetState.customGetters = stateGetters(_originalGetState);
    return newGetState;
};

export default getStateEnhancer;