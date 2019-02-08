import columnService from 'Product/Components/ProductList/Column/service';
    
    
    let columnActions = (function() {
        return {
            generateColumnSettings: (features) => {
                return function(dispatch, getState) {
                    let columnSettings = columnService.generateColumnSettings(
                        getState.customGetters.getAccounts().accounts,
                        getState.customGetters.getVat(),
                        features
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
