import columnService from 'Product/Components/ProductList/Column/service';
    
    
    let columnActions = (function() {
        return {
            generateColumnSettings: () => {
                return function(dispatch, getState) {
                    let accounts = getState.customGetters.getAccounts();
                    let columnSettings = columnService.generateColumnSettings(
                        accounts.features,
                        accounts.accounts,
                        getState.customGetters.getVat(),
                        getState.customGetters.getPickLocationNames()
                    );
                    dispatch({
                        type: "COLUMNS_GENERATE_SETTINGS",
                        payload: {
                            columnSettings
                        }
                    });
                }
            },
        };
    })();
    
    export default columnActions;
