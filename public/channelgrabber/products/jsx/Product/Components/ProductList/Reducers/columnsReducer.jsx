import reducerCreator from 'Common/Reducers/creator';

let initialState = {
    columnSettings: []
};

let ColumnsReducer = reducerCreator(initialState, {
    "COLUMNS_GENERATE_SETTINGS": function(state, action) {
        return Object.assign({}, state, {
            columnSettings: action.payload.columnSettings
        });
    }
});

export default ColumnsReducer;
