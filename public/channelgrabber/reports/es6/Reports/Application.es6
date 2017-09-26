define([
    'Reports/Sales/Service',
    'showHideFilters'
], function(
    SalesService,
    showHideFilters
) {
    class Application {
        constructor() {
            this.salesService = SalesService;

            this.buildSalesChart = this.buildSalesChart.bind(this);
            showHideFilters();

            $(document).on('showHideFilters-triggered', function() {
                $('#sub-header').toggle();
            });
        }

        buildSalesChart() {
            this.salesService.updateChart();
        }
    }

    return Application;
});
