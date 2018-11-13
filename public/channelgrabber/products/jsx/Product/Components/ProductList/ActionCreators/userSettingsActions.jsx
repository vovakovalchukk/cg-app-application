let userSettingsActions = (function() {
    return {
        storeMetrics: ({lengthUnit,massUnit}) => {
            return {
                type: "METRICS_STORE",
                payload: {
                    lengthUnit,
                    massUnit
                }
            }
        },
    };
})();

export default userSettingsActions;
