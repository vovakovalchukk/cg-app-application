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
        },
        storeLowStockThresholdDefaults: (lowStockThresholdDefault) => {
            return {
                type: "LOW_STOCK_DEFAULT_THRESHOLD",
                payload: {
                    lowStockThresholdToggle: lowStockThresholdDefault.toggle,
                    lowStockThresholdValue: lowStockThresholdDefault.value
                }
            }
        }
    };
})();

export default userSettingsActions;
