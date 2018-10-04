import reducerCreator from 'Common/Reducers/creator';

var initialState = {
    columnSettings: []
};

var ColumnsReducer = reducerCreator(initialState, {
    "COLUMNS_GENERATE_SETTINGS": function(state, action) {
        let newState = Object.assign({}, state, {
            columnSettings: action.payload.columnSettings
        });
        return newState;
    }
});

export default ColumnsReducer