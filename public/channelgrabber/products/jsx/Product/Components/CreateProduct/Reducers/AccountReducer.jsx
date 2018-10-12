import reducerCreator from 'Common/Reducers/creator';
    
    var initialState = {
        taxRates: {}
    };
    var AccountReducer = reducerCreator(initialState, {
        "INITIAL_ACCOUNT_DATA_LOADED": function(state, action) {
            return {
                taxRates: action.payload.taxRates,
                stockModeOptions: action.payload.stockModeOptions
            };
        }
    });

    export default AccountReducer;
