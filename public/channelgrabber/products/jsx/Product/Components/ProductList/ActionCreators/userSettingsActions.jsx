let userSettingsActions = (function() {
    return {
        storeMetrics: ({lengthUnit,massUnit}) => {
            console.log('in storeMetrics');
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
