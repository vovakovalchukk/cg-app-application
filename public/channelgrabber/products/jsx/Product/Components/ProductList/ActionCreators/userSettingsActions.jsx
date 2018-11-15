let userSettingsActions = (function() {
    return {
        storeMetrics: ({lengthUnit, massUnit}) => {
            return {
                type: "METRICS_STORE",
                payload: {
                    lengthUnit,
                    massUnit
                }
            }
        },
        storeStockDefaults: (stockModeDefault, stockLevelDefault) => {
            return {
                type: "STOCK_DEFAULTS_STORE",
                payload: {
                    stockModeDefault,
                    stockLevelDefault
                }
            }
        }
    };
})();

export default userSettingsActions;
