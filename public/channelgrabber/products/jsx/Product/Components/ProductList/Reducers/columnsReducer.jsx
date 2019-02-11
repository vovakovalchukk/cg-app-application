import reducerCreator from 'Common/Reducers/creator';

var initialState = {
    columnSettings: []
};

let incPOStockInAvailCol = {
    key: 'includePurchaseOrdersInAvailable',
    width: 200,
    headerText: 'Include quantity on Purchase Orders in available stock',
    fixed: false,
    tab: 'stock',
    align: 'center'
};

var ColumnsReducer = reducerCreator(initialState, {
    "COLUMNS_GENERATE_SETTINGS": function(state, action) {
        let newState = Object.assign({}, state, {
            columnSettings: action.payload.columnSettings
        });
        return newState;
    },
    "INC_PO_STOCK_IN_AVAIL_COL_SHOW": function(state) {
        if (doesIncPOStockInAvailColExistOnState(state)) {
            return state;
        }
        let newCols = state.columnSettings;
        newCols.push(incPOStockInAvailCol);
        return Object.assign({}, state, {
            columnSettings: newCols
        });
    }
});

export default ColumnsReducer

function doesIncPOStockInAvailColExistOnState(state) {
    return !!state.columnSettings.find(column => {
        return column.key === 'includePurchaseOrdersInAvailable';
    });
}