import rowActions from 'Product/Components/ProductList/ActionCreators/rowActions'

let tabActions = (function() {
    return {
        changeTab: (desiredTabKey) => {
            return function(dispatch, getState) {
                let state = getState();
                let numberOfVisibleFixedColumns = getState.customGetters.getVisibleFixedColumns(state).length;
                dispatch({
                    type: "TAB_CHANGE",
                    payload: {
                        desiredTabKey,
                        numberOfVisibleFixedColumns
                    }
                });
                dispatch(rowActions.modifyZIndexOfRows);
            }
        },
        showStockTab: () => {
            return function (dispatch) {
                dispatch({
                    type: 'SHOW_STOCK_TAB',
                    payload: {}
                })
            }
        }
    };
})();

export default tabActions;
