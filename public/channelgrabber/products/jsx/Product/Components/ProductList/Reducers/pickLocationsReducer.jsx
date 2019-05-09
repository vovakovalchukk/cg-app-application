import reducerCreator from 'Common/Reducers/creator';

let initialState = {
    names: [],
    values: {},
    selected: null,
    byProductId: {}
};

let pickLocationsReducer = reducerCreator(initialState, {
    "PICK_LOCATION_SET_NAMES": function(state, action) {
        let names = action.names;
        return Object.assign({}, state, {names});
    },
    "PICK_LOCATION_SET_VALUES": function(state, action) {
        let values = action.values;
        return Object.assign({}, state, {values});
    "PICK_LOCATION_SET_PRODUCT_SUCCESS": function(state, action) {
        n.success("Picking location assigned to product.");
        let {productId, productPickLocations, level, value} = action;
        return Object.assign({}, state, {
            values: appendPickLocationValue(state, level, value),
            byProductId: setPickLocationValue(state, productId, productPickLocations)
        });
    },
    "PICK_LOCATION_SET_PRODUCT_FAILURE": function(state, action) {
        let {err} = action;
        n.ajaxError(err);
        return state;
    }
});

export default pickLocationsReducer;

function appendPickLocationValue(state, level, value) {
    let values = Object.assign({}, state.values);
    if (!values.hasOwnProperty(level)) {
        values[level] = [];
    }
    if (!values[level].includes(value)) {
        values[level].push(value);
        values[level].sort();
    }
    return values;
}

function setPickLocationValue(state, productId, productPickLocations) {
    let byProductId = Object.assign({}, state.byProductId);
    byProductId[productId] = productPickLocations;
    return byProductId;
}