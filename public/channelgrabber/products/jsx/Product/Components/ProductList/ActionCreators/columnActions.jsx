import columnService from 'Product/Components/ProductList/Column/service';
    
    
    let columnActions = (function() {
        return {
            generateColumnSettings: () => {
                return function(dispatch, getState) {
                    let columnSettings = columnService.generateColumnSettings(
                        getState.customGetters.getAccounts().accounts,
                        getState.customGetters.getVat()
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
